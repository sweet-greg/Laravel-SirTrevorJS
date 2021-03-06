<?php

/**
 * Laravel-SirTrevorJs.
 *
 * @link https://github.com/caouecs/Laravel-SirTrevorJs
 */

namespace Caouecs\Sirtrevorjs;

use ParsedownExtra;

/**
 * Class Converter.
 *
 * A Sir Trevor to HTML conversion helper for PHP
 * inspired by work of Wouter Sioen <info@woutersioen.be>
 */
class SirTrevorJsConverter
{
    /**
     * Configuration.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Parser.
     *
     * @var ParsedownExtra
     */
    protected $parser;

    /**
     * View.
     *
     * @var string
     */
    protected $view;

    /**
     * Valid blocks with converter.
     *
     * @var array
     */
    protected $blocks = [
        'blockquote'    => 'Text',
        'embedly'       => 'Embed',
        'facebook'      => 'Social',
        'gettyimages'   => 'Image',
        'heading'       => 'Text',
        'iframe'        => 'Embed',
        'image'         => 'Image',
        'issuu'         => 'Presentation',
        'list'          => 'Text',
        'markdown'      => 'Text',
        'pinterest'     => 'Image',
        'quote'         => 'Text',
        'sketchfab'     => 'Modelisation',
        'slideshare'    => 'Presentation',
        'soundcloud'    => 'Sound',
        'spotify'       => 'Sound',
        'text'          => 'Text',
        'tweet'         => 'Social',
        'video'         => 'Video',
    ];

    /**
     * Custom blocks.
     *
     * @var array
     */
    protected $customBlocks = [];

    /**
     * Construct.
     *
     * @param string $view View
     *
     * @todo  Inject Parser
     */
    public function __construct($view = null)
    {
        $this->config = config('sir-trevor-js');
        $this->parser = new ParsedownExtra();
        $this->view = $view;
    }

    /**
     * Set view.
     *
     * @param string $view
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * Converts the outputted json from Sir Trevor to Html.
     *
     * @param string $json
     *
     * @return string
     */
    public function toHtml($json)
    {
        if (empty($this->view)) {
            $this->view = 'sirtrevorjs::html';
        }

        return $this->convert($json);
    }

    /**
     * Converts the outputted json from Sir Trevor to Amp.
     *
     * @param string $json
     *
     * @return string
     */
    public function toAmp($json)
    {
        if (empty($this->view)) {
            $this->view = 'sirtrevorjs::amp';
        }

        return $this->convert($json, false);
    }

    /**
     * Converts the outputted json from Sir Trevor to Facebook Articles.
     *
     * @param string $json
     *
     * @return string
     */
    public function toFb($json)
    {
        if (empty($this->view)) {
            $this->view = 'sirtrevorjs::fb';
        }

        return $this->convert($json);
    }

    /**
     * Convert the outputted json from Sir Trevor.
     *
     * @param string $json
     * @param bool   $externalJs
     *
     * @return string
     */
    public function convert($json, $externalJs = true)
    {
        // convert the json to an associative array
        $input = json_decode($json, true);
        $text = null;
        $codejs = null;

        if (empty($this->view)) {
            $this->view = 'sirtrevorjs::html';
        }

        if (is_array($input)) {
            // blocks
            $blocks = $this->getBlocks();

            // loop trough the data blocks
            foreach ($input['data'] as $block) {
                // no data, problem
                if (!isset($block['data']) || !array_key_exists($block['type'], $blocks)) {
                    break;
                }

                $class = $blocks[$block['type']];

                $converter = new $class($this->parser, $this->config, $block);
                $converter->setView($this->view);
                $text .= $converter->render($codejs);
            }

            // code js
            if ($externalJs && is_array($codejs)) {
                $text .= implode($codejs);
            }
        }

        return $text;
    }

    /**
     * Return base blocks and custom blocks with good classes.
     *
     * @return array
     */
    protected function getBlocks()
    {
        $blocks = null;

        foreach ($this->blocks as $key => $value) {
            $blocks[$key] = 'Caouecs\\Sirtrevorjs\\Converter\\'.$value.'Converter';
        }

        if (!empty($this->customBlocks)) {
            $blocks = array_merge($blocks, $this->customBlocks);
        }

        if (isset($this->config['customBlocks']) && !empty($this->config['customBlocks'])) {
            $blocks = array_merge($blocks, $this->config['customBlocks']);
        }

        return $blocks;
    }
}
