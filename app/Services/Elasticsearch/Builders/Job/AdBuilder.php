<?php

namespace Coyote\Services\Elasticsearch\Builders\Job;

use Coyote\Services\Elasticsearch\Functions\Random;
use Coyote\Services\Elasticsearch\QueryBuilder;

class AdBuilder extends SearchBuilder
{
    /**
     * @var string|null
     */
    protected $sessionId = null;

    /**
     * @param string $sessionId
     */
    public function setSessionId(string $sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * @return array
     */
    public function build()
    {
        $this->setupFilters();
        $this->setupScoreFunctions();

        $this->score(new Random($this->sessionId));
        $this->size(0, 4);

        return QueryBuilder::build();
    }

    public function setSort($sort)
    {
        $this->sort($sort);
    }
}