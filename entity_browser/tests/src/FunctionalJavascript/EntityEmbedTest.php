<?php

namespace Drupal\Tests\entity_browser\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests entity browser within entity embed.
 *
 * @group entity_browser
 *
 * @package Drupal\Tests\entity_browser\FunctionalJavascript
 */
class EntityEmbedTest extends WebDriverTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'entity_browser',
    'entity_browser_test',
    'embed',
    'entity_embed',
    'entity_browser_entity_embed_test',
  ];

  /**
   * The test administrative user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser([
      'access content',
      'use text format full_html',
      'create test_entity_embed content',
      'access widget_context_default_value entity browser pages',
    ]);
  }

  /**
   * Tests the EntityBrowserWidgetContext argument_default views plugin.
   */
  public function testContextualFilter() {

    $this->drupalLogin($this->adminUser);

    $this->createNode(['type' => 'shark', 'title' => 'Luke']);
    $this->createNode(['type' => 'jet', 'title' => 'Leia']);
    $this->createNode(['type' => 'article', 'title' => 'Darth']);

    $this->drupalGet('/node/add/test_entity_embed');
    $this->assertSession()->waitForElement('css', 'a.cke_button__jet_shark_embed')->click();
    $this->assertSession()->waitForId('views-exposed-form-widget-context-default-value-entity-browser-1');

    $this->getSession()->switchToIFrame('entity_browser_iframe_widget_context_default_value');

    // Check that only nodes of an allowed type are listed.
    $this->assertSession()->responseContains('Luke');
    $this->assertSession()->responseContains('Leia');
    $this->assertSession()->responseNotContains('Darth');

    // Change the allowed bundles on the entity embed.
    $embed_button = $this->container->get('entity_type.manager')
      ->getStorage('embed_button')
      ->load('jet_shark_embed');
    $type_settings = $embed_button->getTypeSettings();
    $type_settings['bundles'] = [
      'article' => 'article',
    ];
    $embed_button->set('type_settings', $type_settings);
    $embed_button->save();

    // Test the new bundle settings are affecting what is visible in the view.
    $this->drupalGet('/node/add/test_entity_embed');
    $this->assertSession()->waitForElement('css', 'a.cke_button__jet_shark_embed')->click();
    $this->assertSession()->waitForId('views-exposed-form-widget-context-default-value-entity-browser-1');

    $this->getSession()->switchToIFrame('entity_browser_iframe_widget_context_default_value');

    // Check that only nodes of an allowed type are listed.
    $this->assertSession()->responseNotContains('Luke');
    $this->assertSession()->responseNotContains('Leia');
    $this->assertSession()->responseContains('Darth');

  }

}
