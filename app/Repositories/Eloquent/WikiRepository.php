<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\SubscribableInterface;
use Coyote\Repositories\Contracts\WikiRepositoryInterface;
use Coyote\Wiki;
use Illuminate\Http\Request;

/**
 * @method $this withTrashed()
 */
class WikiRepository extends Repository implements WikiRepositoryInterface, SubscribableInterface
{
    /**
     * @return \Coyote\Wiki
     */
    public function model()
    {
        return 'Coyote\Wiki';
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function findByPath($path)
    {
        $this->applyCriteria();

        // we need to get page by path. there can be more than one page of giving location.
        // one can be deleted but we have to retrieve the newest one.
        return $this
            ->model
            ->where('path', $path)
            ->orderBy('wiki_id', 'DESC') // <-- DO NOT remove this line
            ->first();
    }

    /**
     * Get children articles of given parent_id.
     *
     * @param int|null $parentId
     * @return mixed
     */
    public function children($parentId = null)
    {
        return $this->prepareChildren($parentId)->get();
    }

    /**
     * @param int|null $parentId
     * @return mixed
     */
    public function getCatalog($parentId = null)
    {
        $comments = $this
            ->app
            ->make(Wiki\Comment::class)
            ->select([$this->raw('COUNT(*)')])
            ->where('wiki_id', '=', $this->raw('wiki.id'))
            ->toSql();

        return $this
            ->prepareChildren($parentId)
            ->select([
                'wiki.*',
                'users.name AS user_name',
                'users.photo',
                'users.id AS user_id',
                $this->raw("($comments) AS comments")
            ])
            ->join('users', function ($join) {
                $sub = $this
                        ->app
                        ->make(Wiki\Log::class)
                        ->select(['user_id'])
                        ->where('wiki_id', '=', $this->raw('wiki.wiki_id'))
                        ->orderBy('id')
                        ->limit(1)
                        ->toSql();

                $join->on('users.id', '=', $this->raw("($sub)"));
            })
            ->where('parent_id', $parentId)
            ->where('children', 0)
            ->paginate();
    }

    /**
     * @param int $pathId
     * @return mixed
     */
    public function parents($pathId)
    {
        $this->applyCriteria();
        return $this->model->from($this->rawFunction('wiki_parents', $pathId))->get();
    }

    /**
     * @return array
     */
    public function treeList()
    {
        $this->applyCriteria();

        $result = [];
        $data = $this->model->from($this->rawFunction('wiki_children'))->get(['id', 'title', 'depth']);

        foreach ($data as $row) {
            $result[$row['id']] = str_repeat('&nbsp;', $row['depth'] * 4) . $row['title'];
        }

        return $result;
    }

    /**
     * @param int $userId
     * @return mixed
     */
    public function getSubscribed($userId)
    {
        return $this
            ->app
            ->make(Wiki\Subscriber::class)
            ->select()
            ->join('wiki', 'wiki.wiki_id', '=', 'wiki_subscribers.wiki_id')
            ->where('wiki_subscribers.user_id', $userId)
            ->whereNull('deleted_at')
            ->orderBy('wiki_subscribers.id', 'DESC')
            ->paginate();
    }

    /**
     * @param \Coyote\Wiki $wiki
     * @param Request $request
     */
    public function save($wiki, Request $request)
    {
        /** @var \Coyote\Wiki\Page $page */
        $page = $this->app->make(Wiki\Page::class)->findOrNew($wiki->wiki_id);

        $page->fill($request->all());
        $page->fillGuarded($request->only(['is_locked', 'template']), $request->user()->can('wiki-admin'));

        // we need to know if those attributes were changed. if so, we need to add new record to the history.
        $isDirty = $page->isDirty(['title', 'excerpt', 'text']);
        $page->save();

        if ($isDirty) {
            // add new version to the history
            $page->logs()->create($page->toArray() + [
                'user_id'   => $request->user()->id,
                'ip'        => $request->ip(),
                'host'      => gethostbyaddr($request->ip()),
                'browser'   => $request->browser(),
                'length'    => mb_strlen($page->text)
            ]);
        }

        if ($page->wasRecentlyCreated) {
            $parent = $this->app->make(Wiki\Path::class)->findOrNew((int) $request->input('parent_id'));
            $wiki->forceFill($page->createPath($parent, $page->slug)->toArray());

            $wiki->id = $wiki->path_id;
        }

        $wiki->forceFill(array_except($page->toArray(), ['id']));
        $wiki->wasRecentlyCreated = $page->wasRecentlyCreated;
    }

    /**
     * @param int $wikiId
     * @param int $pathId
     * @return \Coyote\Wiki\Path
     */
    public function clone($wikiId, $pathId)
    {
        $parent = $this->getPath($pathId);
        $page = $this->getPage($wikiId);

        return $page->createPath($parent, $page->slug);
    }

    /**
     * @param int $id   Current path id
     * @param int $wikiId   Current page id
     * @param int|null $pathId   New path id
     * @return \Coyote\Wiki\Path
     */
    public function move($id, $wikiId, $pathId)
    {
        // current path
        $current = $this->getPath($id);

        // new path
        $path = $this->getPath($pathId);
        // current page
        $page = $this->getPage($wikiId);

        $current->update(['path' => $page->makePath($path->path, $page->slug), 'parent_id' => $pathId]);
        return $current;
    }

    /**
     * @param int $wikiId
     * @return mixed
     */
    public function delete($wikiId)
    {
        return $this->app->make(Wiki\Page::class)->destroy($wikiId);
    }

    /**
     * @param int $pathId
     * @return mixed
     */
    public function unlink($pathId)
    {
        return $this->app->make(Wiki\Path::class)->destroy($pathId);
    }

    /**
     * @param int $pathId
     * @return mixed
     */
    public function restore($pathId)
    {
        return $this->app->make(Wiki\Path::class)->withTrashed()->findOrFail($pathId)->restore();
    }

    /**
     * @param int $wikiId
     * @return Wiki[]
     */
    public function getAllCategories($wikiId)
    {
        return $this
            ->model
            ->select(['parent.*'])
            ->where('wiki.wiki_id', $wikiId)
            ->join('wiki AS parent', 'parent.id', '=', 'wiki.parent_id')
            ->get();
    }

    /**
     * @param int $pathId
     * @return \Coyote\Wiki\Path
     */
    private function getPath($pathId)
    {
        // findOrNew() because $pathId can be null.
        return $this->app->make(Wiki\Path::class)->findOrNew((int) $pathId);
    }

    /**
     * @param int $wikiId
     * @return \Coyote\Wiki\Page
     */
    private function getPage($wikiId)
    {
        return $this->app->make(Wiki\Page::class)->find($wikiId);
    }

    /**
     * @param $name
     * @param array ...$args
     * @return \Illuminate\Database\Query\Expression
     */
    private function rawFunction($name, ...$args)
    {
        foreach ($args as &$arg) {
            if ($arg === null) {
                $arg = 'NULL';
            }
        }

        return $this->raw(sprintf('%s(%s) AS "wiki"', $name, implode(',', $args)));
    }

    /**
     * @param int $parentId
     * @return mixed
     */
    private function prepareChildren($parentId)
    {
        $this->applyCriteria();

        return $this->model->from($this->rawFunction('wiki_children', $parentId));
    }
}
