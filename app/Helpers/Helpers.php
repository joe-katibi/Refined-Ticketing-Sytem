<?php

namespace App\Helpers;

use Config;
use Illuminate\Support\Str;

class Helpers
{
  public static function appClasses()
  {

    $data = config('custom.custom');


    // default data array
    $DefaultData = [
      'myLayout' => 'vertical',
      'myTheme' => 'theme-default',
      'myStyle' => 'dark',
      'myRTLSupport' => true,
      'myRTLMode' => true,
      'hasCustomizer' => true,
      'showDropdownOnHover' => true,
      'displayCustomizer' => true,
      'contentLayout' => 'compact',
      'headerType' => 'fixed',
      'navbarType' => 'fixed',
      'menuFixed' => true,
      'menuCollapsed' => false,
      'footerFixed' => false,
      'fontSize' => 'text-sm',
      'customizerControls' => [
        'rtl',
      'style',
      'headerType',
      'contentLayout',
      'layoutCollapsed',
      'showDropdownOnHover',
      'layoutNavbarOptions',
      'themes',
      ],
      //   'defaultLanguage'=>'en',
    ];

    // if any key missing of array from custom.php file it will be merge and set a default value from dataDefault array and store in data variable
    $data = array_merge($DefaultData, $data);

    // All options available in the template
    $allOptions = [
      'myLayout' => ['vertical', 'horizontal', 'blank', 'front'],
      'menuCollapsed' => [true, false],
      'hasCustomizer' => [true, false],
      'showDropdownOnHover' => [true, false],
      'displayCustomizer' => [true, false],
      'contentLayout' => ['compact', 'wide'],
      'headerType' => ['fixed', 'static'],
      'navbarType' => ['fixed', 'static', 'hidden'],
      'myStyle' => ['light', 'dark', 'system'],
      'myTheme' => ['theme-default', 'theme-bordered', 'theme-semi-dark'],
      'myRTLSupport' => [true, false],
      'myRTLMode' => [true, false],
      'menuFixed' => [true, false],
      'footerFixed' => [true, false],
      'fontSize' => ['text-xs', 'text-sm', 'text-base'],
      'customizerControls' => [],
      // 'defaultLanguage'=>array('en'=>'en','fr'=>'fr','de'=>'de','pt'=>'pt'),
    ];

    //if myLayout value empty or not match with default options in custom.php config file then set a default value
    foreach ($allOptions as $key => $value) {
      if (array_key_exists($key, $DefaultData)) {
        if (gettype($DefaultData[$key]) === gettype($data[$key])) {
          // data key should be string
          if (is_string($data[$key])) {
            // data key should not be empty
            if (isset($data[$key]) && $data[$key] !== null) {
              // data key should not be exist inside allOptions array's sub array
              if (!array_key_exists($data[$key], $value)) {
                // ensure that passed value should be match with any of allOptions array value
                $result = array_search($data[$key], $value, 'strict');
                if (empty($result) && $result !== 0) {
                  $data[$key] = $DefaultData[$key];
                }
              }
            } else {
              // if data key not set or
              $data[$key] = $DefaultData[$key];
            }
          }
        } else {
          $data[$key] = $DefaultData[$key];
        }
      }
    }
    $styleVal = $data['myStyle'] == "dark" ? "dark" : "light";
    if (isset($_COOKIE['style'])) {
      $styleVal = $_COOKIE['style'];
    }
    
    // Handle font size from cookie or default
    $fontSizeVal = $data['fontSize'];
    if (isset($_COOKIE['fontSize'])) {
      $fontSizeVal = $_COOKIE['fontSize'];
    }
    //layout classes
    $layoutClasses = [
      'layout' => $data['myLayout'],
      'theme' => $data['myTheme'],
      'style' => $styleVal,
      'styleOpt' => $data['myStyle'],
      'rtlSupport' => $data['myRTLSupport'],
      'rtlMode' => $data['myRTLMode'],
      'textDirection' => $data['myRTLMode'],
      'menuCollapsed' => $data['menuCollapsed'],
      'hasCustomizer' => $data['hasCustomizer'],
      'showDropdownOnHover' => $data['showDropdownOnHover'],
      'displayCustomizer' => $data['displayCustomizer'],
      'contentLayout' => $data['contentLayout'],
      'headerType' => $data['headerType'],
      'navbarType' => $data['navbarType'],
      'menuFixed' => $data['menuFixed'],
      'footerFixed' => $data['footerFixed'],
      'customizerControls' => $data['customizerControls'],
      'fontSize' => $fontSizeVal,
    ];

    // sidebar Collapsed
    if ($layoutClasses['menuCollapsed'] == true) {
      $layoutClasses['menuCollapsed'] = 'layout-menu-collapsed';
    }

    // Header Type
    if ($layoutClasses['headerType'] == 'fixed') {
      $layoutClasses['headerType'] = 'layout-menu-fixed';
    }
    // Navbar Type
    if ($layoutClasses['navbarType'] == 'fixed') {
      $layoutClasses['navbarType'] = 'layout-navbar-fixed';
    } elseif ($layoutClasses['navbarType'] == 'static') {
      $layoutClasses['navbarType'] = '';
    } else {
      $layoutClasses['navbarType'] = 'layout-navbar-hidden';
    }

    // Menu Fixed
    if ($layoutClasses['menuFixed'] == true) {
      $layoutClasses['menuFixed'] = 'layout-menu-fixed';
    }


    // Footer Fixed
    if ($layoutClasses['footerFixed'] == true) {
      $layoutClasses['footerFixed'] = 'layout-footer-fixed';
    }

    // RTL Supported template
    if ($layoutClasses['rtlSupport'] == true) {
      $layoutClasses['rtlSupport'] = '/rtl';
    }

    // RTL Layout/Mode
    if ($layoutClasses['rtlMode'] == true) {
      $layoutClasses['rtlMode'] = 'rtl';
      $layoutClasses['textDirection'] = 'rtl';
    } else {
      $layoutClasses['rtlMode'] = 'ltr';
      $layoutClasses['textDirection'] = 'ltr';
    }

    // Show DropdownOnHover for Horizontal Menu
    if ($layoutClasses['showDropdownOnHover'] == true) {
      $layoutClasses['showDropdownOnHover'] = true;
    } else {
      $layoutClasses['showDropdownOnHover'] = false;
    }

    // To hide/show display customizer UI, not js
    if ($layoutClasses['displayCustomizer'] == true) {
      $layoutClasses['displayCustomizer'] = true;
    } else {
      $layoutClasses['displayCustomizer'] = false;
    }

    return $layoutClasses;
  }

  public static function updatePageConfig($pageConfigs)
  {
    $demo = 'custom';
    if (isset($pageConfigs)) {
      if (count($pageConfigs) > 0) {
        foreach ($pageConfigs as $config => $val) {
          Config::set('custom.' . $demo . '.' . $config, $val);
        }
      }
    }
  }

    public static function filterMenuByPermission($menu, $userPermissions) {
        return array_filter($menu, function ($item) use ($userPermissions) {
            // Check top-level permission
            if (isset($item['permission']) && !in_array($item['permission'], $userPermissions)) {
                return false;
            }

            // Check submenus recursively
            if (isset($item['submenu'])) {
                $item['submenu'] = self::filterMenuByPermission($item['submenu'], $userPermissions);
            }

            return true;
        });
    }

    function print_pre($array, $exit = false, $__FILE__ = NULL, $__LINE__ = NULL, $__METHOD__ = NULL)
    {
        echo "<pre>";
        echo $__FILE__ . '<br>';
        if (is_array($array)) {
            echo "<hr>";
            echo "Records \t:" . (count($array, 0));
            echo "<br>";
            echo "Data \t\t:" . (count($array, 1));
  
            echo "<hr>";
        } else {
            strlen($array);
        }
        print_r($array);
        echo $__FILE__ . '<br>';
        echo $__LINE__ . '<br>';
        echo $__METHOD__ . '<br>';
  
  
        if ($exit) {
            exit("</pre>");
        } else {
            echo "</pre>";
        }
    }
  

}

if (!function_exists('print_pre')) {
  /**
   *
   *
   * @param array $array
   * @param bool $exit
   * @param null $__FILE__
   * @param null $__LINE__
   * @param null $__METHOD__
   * @return void
   */
  function print_pre($array, $exit = false, $__FILE__ = NULL, $__LINE__ = NULL, $__METHOD__ = NULL)
  {
      echo "<pre>";
      echo $__FILE__ . '<br>';
      if (is_array($array)) {
          echo "<hr>";
          echo "Records \t:" . (count($array, 0));
          echo "<br>";
          echo "Data \t\t:" . (count($array, 1));

          echo "<hr>";
      } else {
          strlen($array);
      }
      print_r($array);
      echo $__FILE__ . '<br>';
      echo $__LINE__ . '<br>';
      echo $__METHOD__ . '<br>';


      if ($exit) {
          exit("</pre>");
      } else {
          echo "</pre>";
      }
  }
}
