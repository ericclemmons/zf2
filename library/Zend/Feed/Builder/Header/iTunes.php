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
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @namespace
 */
namespace Zend\Feed\Builder\Header;
use Zend\Feed\Builder;

/**
 * ITunes rss extension
 *
 * Classes used to describe the itunes channel extension
 *
 * @uses       ArrayObject
 * @uses       \Zend\Feed\Builder\Exception
 * @uses       \Zend\Validator\EmailAddress
 * @category   Zend
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class iTunes extends \ArrayObject
{
    /**
     * Constructor
     *
     * @param  array $categories Categories columns and in iTunes Music Store Browse
     * @return void
     */
    public function __construct(array $categories)
    {
        $this->setCategories($categories);
    }

    /**
     * Sets the categories column and in iTunes Music Store Browse
     * $categories must conform to the following format:
     * <code>
     * array(array('main' => 'main category',
     *             'sub' => 'sub category' // optionnal
     *            ),
     *       // up to 3 rows
     *      )
     * </code>
     *
     * @param  array $categories
     * @return \Zend\Feed\Builder\Header\iTunes
     * @throws \Zend\Feed\Builder\Exception
     */
    public function setCategories(array $categories)
    {
        $nb = count($categories);
        if (0 === $nb) {
            throw new Builder\Exception("you have to set at least one itunes category");
        }
        if ($nb > 3) {
            throw new Builder\Exception("you have to set at most three itunes categories");
        }
        foreach ($categories as $i => $category) {
            if (empty($category['main'])) {
                throw new Builder\Exception("you have to set the main category (category #$i)");
            }
        }
        $this->offsetSet('category', $categories);
        return $this;
    }

    /**
     * Sets the artist value, default to the feed's author value
     *
     * @param  string $author
     * @return \Zend\Feed\Builder\Header\iTunes
     */
    public function setAuthor($author)
    {
        $this->offsetSet('author', $author);
        return $this;
    }

    /**
     * Sets the owner of the postcast
     *
     * @param  string $name  default to the feed's author value
     * @param  string $email default to the feed's email value
     * @return \Zend\Feed\Builder\Header\iTunes
     * @throws \Zend\Feed\Builder\Exception
     */
    public function setOwner($name = '', $email = '')
    {
        if (!empty($email)) {
            $validate = new \Zend\Validator\EmailAddress();
            if (!$validate->isValid($email)) {
                throw new Builder\Exception("you have to set a valid email address into the itunes owner's email property");
            }
        }
        $this->offsetSet('owner', array('name' => $name, 'email' => $email));
        return $this;
    }

    /**
     * Sets the album/podcast art picture
     * Default to the feed's image value
     *
     * @param  string $image
     * @return \Zend\Feed\Builder\Header\iTunes
     */
    public function setImage($image)
    {
        $this->offsetSet('image', $image);
        return $this;
    }

    /**
     * Sets the short description of the podcast
     * Default to the feed's description
     *
     * @param  string $subtitle
     * @return \Zend\Feed\Builder\Header\iTunes
     */
    public function setSubtitle($subtitle)
    {
        $this->offsetSet('subtitle', $subtitle);
        return $this;
    }

    /**
     * Sets the longer description of the podcast
     * Default to the feed's description
     *
     * @param  string $summary
     * @return \Zend\Feed\Builder\Header\iTunes
     */
    public function setSummary($summary)
    {
        $this->offsetSet('summary', $summary);
        return $this;
    }

    /**
     * Prevent a feed from appearing
     *
     * @param  string $block can be 'yes' or 'no'
     * @return \Zend\Feed\Builder\Header\iTunes
     * @throws \Zend\Feed\Builder\Exception
     */
    public function setBlock($block)
    {
        $block = strtolower($block);
        if (!in_array($block, array('yes', 'no'))) {
            throw new Builder\Exception("you have to set yes or no to the itunes block property");
        }
        $this->offsetSet('block', $block);
        return $this;
    }

    /**
     * Configuration of the parental advisory graphic
     *
     * @param  string $explicit can be 'yes', 'no' or 'clean'
     * @return \Zend\Feed\Builder\Header\iTunes
     * @throws \Zend\Feed\Builder\Exception
     */
    public function setExplicit($explicit)
    {
        $explicit = strtolower($explicit);
        if (!in_array($explicit, array('yes', 'no', 'clean'))) {
            throw new Builder\Exception("you have to set yes, no or clean to the itunes explicit property");
        }
        $this->offsetSet('explicit', $explicit);
        return $this;
    }

    /**
     * Sets a comma separated list of 12 keywords maximum
     *
     * @param  string $keywords
     * @return \Zend\Feed\Builder\Header\iTunes
     */
    public function setKeywords($keywords)
    {
        $this->offsetSet('keywords', $keywords);
        return $this;
    }

    /**
     * Sets the new feed URL location
     *
     * @param  string $url
     * @return \Zend\Feed\Builder\Header\iTunes
     */
    public function setNewFeedUrl($url)
    {
        $this->offsetSet('new_feed_url', $url);
        return $this;
    }

    /**
     * Read only properties accessor
     *
     * @param  string $name property to read
     * @return mixed
     */
    public function __get($name)
    {
        if (!$this->offsetExists($name)) {
            return NULL;
        }

        return $this->offsetGet($name);
    }

    /**
     * Write properties accessor
     *
     * @param  string $name  name of the property to set
     * @param  mixed  $value value to set
     * @return void
     */
    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    /**
     * Isset accessor
     *
     * @param  string $key
     * @return boolean
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Unset accessor
     *
     * @param  string $key
     * @return void
     */
    public function __unset($key)
    {
        if ($this->offsetExists($key)) {
            $this->offsetUnset($key);
        }
    }

}
