<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class FeatureContext extends BehatContext {

  private $input, $parsed, $lang;
  /**
   * Initializes context.
   * Every scenario gets it's own context object.
   *
   * @param array $parameters context parameters (set them up through behat.yml)
   */
  public function __construct(array $parameters) {
    // Initialize your context here
  }

  /**
   * @Given /^the input string is "And God said \\"∇×\(∇×F\) = ∇\(∇·F\) − ∇(\d+)F\\" and there was light\."$/
   */
  public function theInputStringIsAndGodSaidXXfFFAndThereWasLight($input) {
    $this->input = $input;
  }

  /**
   * @When /^input string is processed with Typogrowl’s typography parser$/
   */
  public function inputStringIsProcessedWithTypogrowlSTypographyParser() {
    $this->parsed = \Mudasobwa\Typogrowth\Parser::parse($this->input);
  }

  /**
   * @Then /^the typoed result should equal to "([^"]*)"$/
   */
  public function theTypoedResultShouldEqualTo($parsed) {
    if ((string) $parsed !== $this->parsed) {
      throw new Exception(
        "Actual output differs:\n" . $this->parsed
      );
    }
  }

  /**
   * @Given /^the call to string’s typo should equal to "([^"]*)"$/
   */
  public function theCallToStringSTypoShouldEqualTo($arg1) {
    // FIXME Do nothing yet.
  }

  /**
   * @Given /^neither single nor double quotes are left in the string$/
   */
  public function neitherSingleNorDoubleQuotesAreLeftInTheString() {
    if (preg_match_all('/[\'"]/mu', $this->parsed) > 0) {
      throw new Exception(
        "Quotes are left:\n" . $this->parsed
      );
    }
  }

  /**
   * @Given /^the input string is "([^"]*)"$/
   */
  public function theInputStringIs($input) {
    $this->input = $input;
  }

  public function inputStringIsProcessedWithTypogrowlSTypographyParserWithLang($lang) {
    $this->parsed = \Mudasobwa\Typogrowth\Parser::parse($this->input, $lang);
  }

  /**
   * @Given /^the call to string’s typo with lang "([^"]*)" should equal to "([^"]*)"$/
   */
  public function theCallToStringSTypoWithLangShouldEqualTo($arg1, $arg2) {
    // FIXME Do nothing yet.
  }

  /**
   * @Given /^the input string is "([^"]*)" english "([^"]*)"$/
   */
  public function theInputStringIsEnglish($arg1, $arg2) {
    throw new PendingException();
  }

  /**
   * @When /^input string is modified inplace with typo!$/
   */
  public function inputStringIsModifiedInplaceWithTypo() {
    // FIXME Do nothing yet.
  }

  /**
   * @Then /^typoed result should equal to "([^"]*)"$/
   */
  public function typoedResultShouldEqualTo($arg1) {
    throw new PendingException();
  }

  /**
   * @When /^input string language is determined$/
   */
  public function inputStringLanguageIsDetermined() {
    $this->lang = (new \Mudasobwa\Typogrowth\Parser)->is_ru($this->input) ? 'ru' : 'us';
  }

  /**
   * @Then /^the language should equal to "([^"]*)"$/
   */
  public function theLanguageShouldEqualTo($lang) {
    if ($this->lang !== (string)$lang) {
      throw new Exception(
        "Lang is wrong:\n" . $this->lang
      );
    }
  }

  /**
   * @When /^input string is processed with Typogrowl’s typography parser with section "([^"]*)"$/
   */
  public function inputStringIsProcessedWithTypogrowlSTypographyParserWithSection($section) {
    $this->parsed = \Mudasobwa\Typogrowth\Parser::parse($this->input, 'default', array($section));
  }

  /**
   * @Given /^the input string is$/
   */
  public function theInputStringIs2(PyStringNode $string) {
    $this->input = $string;
  }

  /**
   * @Then /^the typoed result should equal to$/
   */
  public function theTypoedResultShouldEqualTo2(PyStringNode $string) {
    if ((string) $string !== $this->parsed) {
      throw new Exception(
        "Actual output differs:\n" . $this->parsed
      );
    }
  }

}
