<?php

namespace Coyote\Services\Parser\Parsers;

class Geshi implements ParserInterface
{
    /**
     * @param string $text
     * @return string
     */
    public function parse($text)
    {
        preg_match_all('|<pre><code class="([a-z\d#-]+)">(.+?)<\/code><\/pre>|s', $text, $matches);

        if (!$matches[1]) {
            return $text;
        }

        $alias = ['html' => 'html5'];

        $geshi = new \Boduch\Geshi\Geshi();
        $geshi->line_ending = "\n";
        /* tekst bedzie zawarty w	znaczniku <pre>	*/
        $geshi->set_header_type(GESHI_HEADER_NONE);

        for ($i = 0, $count = count($matches[1]); $i < $count; $i++) {
            $language = $matches[1][$i];

            // class may have prefix "language". omit it.
            if (substr($language, 0, 8) === 'language') {
                $language = substr($language, 9);
                /* nadaj jezyk kolorowania skladnii */
                $geshi->set_language(isset($alias[$language]) ? $alias[$language] : $language, true);
            }

            $geshi->set_source(htmlspecialchars_decode($matches[2][$i]));

            $text = str_replace(
                $this->tag($language, $matches[2][$i]),
                $this->tag($language, $geshi->parse_code()),
                $text
            );
        }

        return $text;
    }

    /**
     * @param string $language
     * @param string $code
     * @return string
     */
    private function tag($language, $code)
    {
        return sprintf('<pre><code class="language-%s">%s</code></pre>', $language, $code);
    }
}
