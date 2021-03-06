<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 HUBzero Foundation, LLC.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   framework
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace Hubzero\Base\Traits;

use Hubzero\Document\Asset\Image;
use Hubzero\Document\Asset\Javascript;
use Hubzero\Document\Asset\Stylesheet;
use Hubzero\Component\ControllerInterface;
use Hubzero\Plugin\Plugin;
use Hubzero\Module\Module;

/**
 * Asset Aware trait.
 * Adds helpers for pushing CSS and JS assets to the document.
 */
trait AssetAware
{
	/**
	 * Push CSS to the document
	 *
	 * @param   string  $stylesheet  Stylesheet or styles to add
	 * @param   string  $extension   Extension name, e.g.: com_example, mod_example, plg_example_test
	 * @param   array   $attributes  Attributes
	 * @return  object
	 */
	public function css($stylesheet = '', $extension = null, $attributes = array())
	{
		$extension = $extension ?: $this->detectExtensionName();

		$attr = array_merge(array(
			'type'    => 'text/css',
			'media'   => null,
			'attribs' => array()
		), $attributes);

		$asset = new Stylesheet($extension, $stylesheet);

		$asset = $this->isSuperGroupAsset($asset);

		if ($asset->exists())
		{
			if ($asset->isDeclaration())
			{
				\App::get('document')->addStyleDeclaration($asset->contents());
			}
			else
			{
				\App::get('document')->addStyleSheet($asset->link(), $attr['type'], $attr['media'], $attr['attribs']);
			}
		}

		return $this;
	}

	/**
	 * Push JS to the document
	 *
	 * @param   string  $asset       Script to add
	 * @param   string  $extension   Extension name, e.g.: com_example, mod_example, plg_example_test
	 * @param   array   $attributes  Attributes
	 * @return  object
	 */
	public function js($asset = '', $extension = null, $attributes = array())
	{
		$extension = $extension ?: $this->detectExtensionName();

		$attr = array_merge(array(
			'type'  => 'text/javascript',
			'defer' => false,
			'async' => false
		), $attributes);

		$asset = new Javascript($extension, $asset);

		$asset = $this->isSuperGroupAsset($asset);

		if ($asset->exists())
		{
			if ($asset->isDeclaration())
			{
				\App::get('document')->addScriptDeclaration($asset->contents());
			}
			else
			{
				\App::get('document')->addScript($asset->link(), $attr['type'], $attr['defer'], $attr['async']);
			}
		}

		return $this;
	}

	/**
	 * Get the path to an image
	 *
	 * @param   string  $asset      Image name
	 * @param   string  $extension  Extension name, e.g.: com_example, mod_example, plg_example_test
	 * @return  string
	 */
	public function img($asset, $extension = null)
	{
		$extension = $extension ?: $this->detectExtensionName();

		$asset = new Image($extension, $asset);

		return $asset->link();
	}

	/**
	 * Determine the extension the view is being called from
	 *
	 * @return  string
	 */
	private function detectExtensionName()
	{
		if ($this instanceof Plugin)
		{
			return 'plg_' . $this->_type . '_' . $this->_name;
		}
		else if ($this instanceof ControllerInterface)
		{
			return \Request::getCmd('option', $this->_option);
		}
		else if ($this instanceof Module)
		{
			return $this->module->module;
		}

		return '';
	}

	/**
	 * Modify paths if in a super group
	 *
	 * @param   object  $asset  Asset
	 * @return  object
	 */
	protected function isSuperGroupAsset($asset)
	{
		if ($asset->extensionType() != 'components'
		 || $asset->isDeclaration()
		 || $asset->isExternal())
		{
			return $asset;
		}

		if (defined('JPATH_GROUPCOMPONENT'))
		{
			$base = JPATH_GROUPCOMPONENT;

			$asset->setPath('source', $base . DS . 'assets' . DS . $asset->type() . DS . $asset->file());
			//$asset->setPath('source', $base . DS . $asset->file());
		}

		return $asset;
	}
}
