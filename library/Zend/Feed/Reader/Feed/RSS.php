<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @namespace
 */
namespace Zend\Feed\Reader\Feed;
use Zend\Feed\Reader;
use Zend\Date;

/**
 * @uses       \Zend\Date\Date
 * @uses       \Zend\Feed\Exception
 * @uses       \Zend\Feed\Reader\Reader
 * @uses       \Zend\Feed\Reader\Collection\Author
 * @uses       \Zend\Feed\Reader\Extension\Atom\Feed
 * @uses       \Zend\Feed\Reader\Extension\DublinCore\Feed
 * @uses       \Zend\Feed\Reader\FeedAbstract
 * @category   Zend
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class RSS extends Reader\FeedAbstract
{

    /**
     * Constructor
     *
     * @param  DOMDocument $dom
     * @param  string $type
     */
    public function __construct(\DomDocument $dom, $type = null)
    {
        parent::__construct($dom, $type);

        $dublinCoreClass = Reader\Reader::getPluginLoader()->getClassName('DublinCore\Feed');
        $this->_extensions['DublinCore\Feed'] = new $dublinCoreClass($dom, $this->_data['type'], $this->_xpath);
        $atomClass = Reader\Reader::getPluginLoader()->getClassName('Atom\Feed');
        $this->_extensions['Atom\Feed'] = new $atomClass($dom, $this->_data['type'], $this->_xpath);

        if ($this->getType() !== Reader\Reader::TYPE_RSS_10 && $this->getType() !== Reader\Reader::TYPE_RSS_090) {
            $xpathPrefix = '/rss/channel';
        } else {
            $xpathPrefix = '/rdf:RDF/rss:channel';
        }
        foreach ($this->_extensions as $extension) {
            $extension->setXpathPrefix($xpathPrefix);
        }
    }

    /**
     * Get a single author
     *
     * @param  int $index
     * @return string|null
     */
    public function getAuthor($index = 0)
    {
        $authors = $this->getAuthors();

        if (isset($authors[$index])) {
            return $authors[$index];
        }

        return null;
    }

    /**
     * Get an array with feed authors
     *
     * @return array
     */
    public function getAuthors()
    {
        if (array_key_exists('authors', $this->_data)) {
            return $this->_data['authors'];
        }
        
        $authors = array();
        $authors_dc = $this->getExtension('DublinCore')->getAuthors();
        if (!empty($authors_dc)) {
            foreach ($authors_dc as $author) {
                $authors[] = array(
                    'name' => $author['name']
                );
            }
        }

        /**
         * Technically RSS doesn't specific author element use at the feed level
         * but it's supported on a "just in case" basis.
         */
        if ($this->getType() !== Reader\Reader::TYPE_RSS_10
        && $this->getType() !== Reader\Reader::TYPE_RSS_090) {
            $list = $this->_xpath->query('//author');
        } else {
            $list = $this->_xpath->query('//rss:author');
        }
        if ($list->length) {
            foreach ($list as $author) {
                $string = trim($author->nodeValue);
                $email = null;
                $name = null;
                $data = array();
                // Pretty rough parsing - but it's a catchall
                if (preg_match("/^.*@[^ ]*/", $string, $matches)) {
                    $data['email'] = trim($matches[0]);
                    if (preg_match('/\((.*)\)$/', $string, $matches)) {
                        $data['name'] = $matches[1];
                    }
                    $authors[] = $data;
                } 
            }
        }

        if (count($authors) == 0) {
            $authors = $this->getExtension('Atom')->getAuthors();
        } else {
            $authors = new Reader\Collection\Author(
                Reader\Reader::arrayUnique($authors)
            );
        }

        if (count($authors) == 0) {
            $authors = null;
        }

        $this->_data['authors'] = $authors;

        return $this->_data['authors'];
    }

    /**
     * Get the copyright entry
     *
     * @return string|null
     */
    public function getCopyright()
    {
        if (array_key_exists('copyright', $this->_data)) {
            return $this->_data['copyright'];
        }

        $copyright = null;

        if ($this->getType() !== Reader\Reader::TYPE_RSS_10 &&
            $this->getType() !== Reader\Reader::TYPE_RSS_090) {
            $copyright = $this->_xpath->evaluate('string(/rss/channel/copyright)');
        }

        if (!$copyright && !is_null($this->getExtension('DublinCore'))) {
            $copyright = $this->getExtension('DublinCore')->getCopyright();
        }

        if (empty($copyright)) {
            $copyright = $this->getExtension('Atom')->getCopyright();
        }

        if (!$copyright) {
            $copyright = null;
        }

        $this->_data['copyright'] = $copyright;

        return $this->_data['copyright'];
    }

    /**
     * Get the feed creation date
     *
     * @return string|null
     */
    public function getDateCreated()
    {
        return $this->getDateModified();
    }

    /**
     * Get the feed modification date
     *
     * @return \Zend\Date\Date
     */
    public function getDateModified()
    {
        if (array_key_exists('datemodified', $this->_data)) {
            return $this->_data['datemodified'];
        }

        $dateModified = null;
        $date = null;

        if ($this->getType() !== Reader\Reader::TYPE_RSS_10 &&
            $this->getType() !== Reader\Reader::TYPE_RSS_090) {
            $dateModified = $this->_xpath->evaluate('string(/rss/channel/pubDate)');
            if (!$dateModified) {
                $dateModified = $this->_xpath->evaluate('string(/rss/channel/lastBuildDate)');
            }
            if ($dateModified) {
                $dateStandards = array(Date\Date::RSS, Date\Date::RFC_822,
                Date\Date::RFC_2822, Date\Date::DATES);
                $date = new Date\Date;
                foreach ($dateStandards as $standard) {
                    try {
                        $date->set($dateModified, $standard);
                        break;
                    } catch (Date\Exception $e) {
                        if ($standard == Date\Date::DATES) {
                            throw new \Zend\Feed\Exception(
                                'Could not load date due to unrecognised'
                                .' format (should follow RFC 822 or 2822):'
                                . $e->getMessage(),
                                0, $e
                            );
                        }
                    }
                }
            }
        }

        if (!$date) {
            $date = $this->getExtension('DublinCore')->getDate();
        }

        if (!$date) {
            $date = $this->getExtension('Atom')->getDateModified();
        }

        if (!$date) {
            $date = null;
        }

        $this->_data['datemodified'] = $date;

        return $this->_data['datemodified'];
    }

    /**
     * Get the feed description
     *
     * @return string|null
     */
    public function getDescription()
    {
        if (array_key_exists('description', $this->_data)) {
            return $this->_data['description'];
        }

        $description = null;

        if ($this->getType() !== Reader\Reader::TYPE_RSS_10 &&
            $this->getType() !== Reader\Reader::TYPE_RSS_090) {
            $description = $this->_xpath->evaluate('string(/rss/channel/description)');
        } else {
            $description = $this->_xpath->evaluate('string(/rdf:RDF/rss:channel/rss:description)');
        }

        if (!$description && !is_null($this->getExtension('DublinCore'))) {
            $description = $this->getExtension('DublinCore')->getDescription();
        }

        if (empty($description)) {
            $description = $this->getExtension('Atom')->getDescription();
        }

        if (!$description) {
            $description = null;
        }

        $this->_data['description'] = $description;

        return $this->_data['description'];
    }

    /**
     * Get the feed ID
     *
     * @return string|null
     */
    public function getId()
    {
        if (array_key_exists('id', $this->_data)) {
            return $this->_data['id'];
        }

        $id = null;

        if ($this->getType() !== Reader\Reader::TYPE_RSS_10 &&
            $this->getType() !== Reader\Reader::TYPE_RSS_090) {
            $id = $this->_xpath->evaluate('string(/rss/channel/guid)');
        }

        if (!$id && !is_null($this->getExtension('DublinCore'))) {
            $id = $this->getExtension('DublinCore')->getId();
        }

        if (empty($id)) {
            $id = $this->getExtension('Atom')->getId();
        }

        if (!$id) {
            if ($this->getLink()) {
                $id = $this->getLink();
            } elseif ($this->getTitle()) {
                $id = $this->getTitle();
            } else {
                $id = null;
            }
        }

        $this->_data['id'] = $id;

        return $this->_data['id'];
    }

    /**
     * Get the feed language
     *
     * @return string|null
     */
    public function getLanguage()
    {
        if (array_key_exists('language', $this->_data)) {
            return $this->_data['language'];
        }

        $language = null;

        if ($this->getType() !== Reader\Reader::TYPE_RSS_10 &&
            $this->getType() !== Reader\Reader::TYPE_RSS_090) {
            $language = $this->_xpath->evaluate('string(/rss/channel/language)');
        }

        if (!$language && !is_null($this->getExtension('DublinCore'))) {
            $language = $this->getExtension('DublinCore')->getLanguage();
        }

        if (empty($language)) {
            $language = $this->getExtension('Atom')->getLanguage();
        }

        if (!$language) {
            $language = $this->_xpath->evaluate('string(//@xml:lang[1])');
        }

        if (!$language) {
            $language = null;
        }

        $this->_data['language'] = $language;

        return $this->_data['language'];
    }

    /**
     * Get a link to the feed
     *
     * @return string|null
     */
    public function getLink()
    {
        if (array_key_exists('link', $this->_data)) {
            return $this->_data['link'];
        }

        $link = null;

        if ($this->getType() !== Reader\Reader::TYPE_RSS_10 &&
            $this->getType() !== Reader\Reader::TYPE_RSS_090) {
            $link = $this->_xpath->evaluate('string(/rss/channel/link)');
        } else {
            $link = $this->_xpath->evaluate('string(/rdf:RDF/rss:channel/rss:link)');
        }

        if (empty($link)) {
            $link = $this->getExtension('Atom')->getLink();
        }

        if (!$link) {
            $link = null;
        }

        $this->_data['link'] = $link;

        return $this->_data['link'];
    }

    /**
     * Get a link to the feed XML
     *
     * @return string|null
     */
    public function getFeedLink()
    {
        if (array_key_exists('feedlink', $this->_data)) {
            return $this->_data['feedlink'];
        }

        $link = null;

        $link = $this->getExtension('Atom')->getFeedLink();

        if (!$link) {
            $link = null;
        }

        $this->_data['feedlink'] = $link;

        return $this->_data['feedlink'];
    }

    /**
     * Get the feed generator entry
     *
     * @return string|null
     */
    public function getGenerator()
    {
        if (array_key_exists('generator', $this->_data)) {
            return $this->_data['generator'];
        }

        $generator = null;

        if ($this->getType() !== Reader\Reader::TYPE_RSS_10 &&
            $this->getType() !== Reader\Reader::TYPE_RSS_090) {
            $generator = $this->_xpath->evaluate('string(/rss/channel/generator)');
        }

        if (!$generator) {
            if ($this->getType() !== Reader\Reader::TYPE_RSS_10 &&
            $this->getType() !== Reader\Reader::TYPE_RSS_090) {
                $generator = $this->_xpath->evaluate('string(/rss/channel/atom:generator)');
            } else {
                $generator = $this->_xpath->evaluate('string(/rdf:RDF/rss:channel/atom:generator)');
            }
            if ($generator) {
                $generator = html_entity_decode($generator, ENT_QUOTES, $this->getEncoding());
            }
        }

        if (empty($generator)) {
            $generator = $this->getExtension('Atom')->getGenerator();
        }

        if (!$generator) {
            $generator = null;
        }

        $this->_data['generator'] = $generator;

        return $this->_data['generator'];
    }

    /**
     * Get the feed title
     *
     * @return string|null
     */
    public function getTitle()
    {
        if (array_key_exists('title', $this->_data)) {
            return $this->_data['title'];
        }

        $title = null;

        if ($this->getType() !== Reader\Reader::TYPE_RSS_10 &&
            $this->getType() !== Reader\Reader::TYPE_RSS_090) {
            $title = $this->_xpath->evaluate('string(/rss/channel/title)');
        } else {
            $title = $this->_xpath->evaluate('string(/rdf:RDF/rss:channel/rss:title)');
        }

        if (!$title && !is_null($this->getExtension('DublinCore'))) {
            $title = $this->getExtension('DublinCore')->getTitle();
        }

        if (!$title) {
            $title = $this->getExtension('Atom')->getTitle();
        }

        if (!$title) {
            $title = null;
        }

        $this->_data['title'] = $title;

        return $this->_data['title'];
    }

    /**
     * Get an array of any supported Pusubhubbub endpoints
     *
     * @return array|null
     */
    public function getHubs()
    {
        if (array_key_exists('hubs', $this->_data)) {
            return $this->_data['hubs'];
        }

        $hubs = $this->getExtension('Atom')->getHubs();

        if (empty($hubs)) {
            $hubs = null;
        } else {
            $hubs = array_unique($hubs);
        }

        $this->_data['hubs'] = $hubs;

        return $this->_data['hubs'];
    }
    
    /**
     * Get all categories
     *
     * @return \Zend\Feed\Reader\Collection\Category
     */
    public function getCategories()
    {
        if (array_key_exists('categories', $this->_data)) {
            return $this->_data['categories'];
        }

        if ($this->getType() !== Reader\Reader::TYPE_RSS_10 &&
            $this->getType() !== Reader\Reader::TYPE_RSS_090) {
            $list = $this->_xpath->query('/rss/channel//category');
        } else {
            $list = $this->_xpath->query('/rdf:RDF/rss:channel//rss:category');
        }

        if ($list->length) {
            $categoryCollection = new Reader\Collection\Category;
            foreach ($list as $category) {
                $categoryCollection[] = array(
                    'term' => $category->nodeValue,
                    'scheme' => $category->getAttribute('domain'),
                    'label' => $category->nodeValue,
                );
            }
        } else {
            $categoryCollection = $this->getExtension('DublinCore')->getCategories();
        }
        
        if (count($categoryCollection) == 0) {
            $categoryCollection = $this->getExtension('Atom')->getCategories();
        }

        $this->_data['categories'] = $categoryCollection;

        return $this->_data['categories'];
    }

    /**
     * Read all entries to the internal entries array
     *
     */
    protected function _indexEntries()
    {
        $entries = array();

        if ($this->getType() !== Reader\Reader::TYPE_RSS_10 && $this->getType() !== Reader\Reader::TYPE_RSS_090) {
            $entries = $this->_xpath->evaluate('//item');
        } else {
            $entries = $this->_xpath->evaluate('//rss:item');
        }

        foreach($entries as $index=>$entry) {
            $this->_entries[$index] = $entry;
        }
    }

    /**
     * Register the default namespaces for the current feed format
     *
     */
    protected function _registerNamespaces()
    {
        switch ($this->_data['type']) {
            case Reader\Reader::TYPE_RSS_10:
                $this->_xpath->registerNamespace('rdf', Reader\Reader::NAMESPACE_RDF);
                $this->_xpath->registerNamespace('rss', Reader\Reader::NAMESPACE_RSS_10);
                break;

            case Reader\Reader::TYPE_RSS_090:
                $this->_xpath->registerNamespace('rdf', Reader\Reader::NAMESPACE_RDF);
                $this->_xpath->registerNamespace('rss', Reader\Reader::NAMESPACE_RSS_090);
                break;
        }
    }
}
