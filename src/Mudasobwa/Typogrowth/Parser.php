<?php

namespace Mudasobwa\Typogrowth;

require_once 'vendor/autoload.php';

class TypogrowthException extends \Exception { }

class Parser {

  const DEFAULT_RULES = 'config/typogrowth.yaml';
  const DEFAULT_SHADOWS = 'config/shadows.yaml';
  
  const HTML_TAG_RE = '#<\s*[A-Za-z][^>]*>#um';
  const URL_TAG_RE  = '#((http|https|ftp|mailto):/?/?(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|"|\'|:|\<|$|\.\s)#ui';
  
  public $rules, $shadows;
  
  public function __construct(
    $rules_file = self::DEFAULT_RULES, $shadows_file = self::DEFAULT_SHADOWS) {
  
    $this->rules = \Spyc::YAMLLoad($rules_file);
    if (sizeof($this->rules) == 1 && $this->rules[0] == $rules_file) {
      throw new TypogrowthException('File ['.$rules_file.'] is not a valid YAML config for '.__CLASS__.'.');
    }
    $this->shadows = array(self::HTML_TAG_RE, self::URL_TAG_RE);
    $shadows = \Spyc::YAMLLoad($shadows_file);
    if (array_key_exists('custom', $shadows)) {
      foreach ($shadows['custom'] as $custom) {
        $this->shadows[] = $custom;
      }
    }
    if (array_key_exists('grip', $shadows)) {
      foreach ($shadows['grip'] as $grip) {
        $this->shadows[] = '#(?<='.$grip.')(.*?)(?='.$grip.')#um';
      }
    }
  }

  private function merge_shadows($shadows) {
    return array_unique(array_merge($shadows, $this->shadows));;
  }
  
  private function str_to_re($str) {
    return '/' . $str . '/mu';
  }
  
  private function is_ru($str, $shadows = array()) {
    $clean = $str;
    foreach ($this->merge_shadows($shadows) as $shadow) {
      $clean = preg_replace($shadow, '', $clean);
    }
    return (preg_match_all('/[А-Яа-я]/eum', $clean) > mb_strlen($clean) / 3);
  }
  
  public function suggest_lang($str, $shadows = array()) {
    return $this->is_ru($str, $shadows) ? 'ru' : 'default';
  }
  
  public function __parse($str, $lang = null, $sections = null, $shadows = array()) {
    $delims = Parser::safe_delimiters($str);
    $shadows = $this->merge_shadows($shadows);
    if (!$lang) { $lang = $this->suggest_lang($str, $shadows); }
    $needed_rules = array();
    if (!$sections) {
      $sections = \array_keys($this->rules);
    }
    foreach($sections as $section) {
      $needed_rules[$section] = $this->rules[$section];
    }
    $needed_rules = \array_reverse($needed_rules);

    $paras = array();
    foreach (explode("\n\n", preg_replace('#[\r\n]#um', "\n", $str)) as $para) {
      foreach($shadows as $shadow) {
        $para = preg_replace_callback(
                $shadow, 
                function ($matches) use ($delims) {
                  return $delims[0] . base64_encode($matches[0]) . $delims[1];
                },
                $para);
      }
      while ($rules = array_pop($needed_rules)) {
        foreach ($rules as $rule) {
          if (!array_key_exists($lang, $rule)) {
            $rule[$lang] = $rule['default'];
            if (!$rule[$lang]) {
              throw new TypogrowthException('No subst for ['.$rule.'] in rules file.');
            }
          }
          $rulere = $this->str_to_re($rule['re']);
          $para = array_key_exists('pattern', $rule) ?
                    preg_replace_callback(
                      $rulere,
                      function ($matches) use ($rule, $lang) { 
                        return \preg_replace('/' . $rule['pattern'] . '/mu', $rule[$lang][0], $matches[0]);
                      }, $para
                    ) :
                    preg_replace($rulere, $rule[$lang][0], $para);
          $prev = '';
          if (sizeof($rule[$lang]) > 1) {
            $para = preg_replace_callback(
                    '/(.*?)('.$rule[$lang][0].')/mu',
                    function ($matches) use ($rules, $rule, $lang, &$prev) {
                      $prev .= $matches[1];
                      $obsoletes = preg_match_all($this->str_to_re(join('|', $rule[$lang])), $prev);
                      if (array_key_exists('compliant', $rule)) {
                        $compliants = array_key_exists($lang, $rules[$rule['compliant']]) ?
                              $rules[$rule['compliant']][$lang] :
                              $rules[$rule['compliant']]['default'];
                        $obsoletes -= preg_match_all($this->str_to_re(join('|', $compliants)), $prev);
                      }
                      $before = preg_match_all($this->str_to_re($rule['original']), $prev);
                      if (array_key_exists('slave', $rule)) {
                        $obsoletes -= $before + 1;
                      } else {
                        $obsoletes += $before;
                      }

                      $subst = $rule[$lang][($obsoletes + (abs($obsoletes))*sizeof($rule[$lang])) % sizeof($rule[$lang])];
                      $prev .= $subst;
                      return $matches[1] . $subst;
                    },
                    $para
            );
          }
        }
      }
      
      $para = preg_replace('/[ ]{2,}/mu', ' ', $para);
      $paras[] = preg_replace_callback(
              '/' . $delims[0] . '(.*?)' . $delims[1] . '/mu', 
              function ($matches) {
                return base64_decode($matches[1]);
              },
              $para);
    }
    return join("\n\n", $paras);
  }
  
  public static function safe_delimiters($str) {
    $delimiters = array('❮', '❯');
    while(true) {
      if (!preg_match('#'.join('|', $delimiters).'#um', $str)) {
        return $delimiters;
      }
      $delimiters = array($delimiters[0].'❮', $delimiters[1].'❯');
    }
    
  }
  
  public static function parse($str, $lang = 'default', $sections = null, $shadows = array()) {
    return (new Parser)->__parse($str, $lang, $sections, $shadows);
  }
}
