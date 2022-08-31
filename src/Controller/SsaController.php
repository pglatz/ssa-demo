<?php
/**
 * @file
 * @author Phil Glatz
 */
namespace Drupal\ssa\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Messenger\MessengerTrait;


/**
 * Provides route responses for the Example module.
 */
class SsaController extends ControllerBase {

  protected $renderer;

  public function __construct(Renderer $renderer) {
    $this->renderer = $renderer;
  }


  public static function create(ContainerInterface $container): SsaController {
    return new static(
      $container->get('renderer')
    );
  }

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function ssaPage() {

    $output = [
      '#theme' => 'ssa-page',
      '#content' => 'ssa content here',
    ];

    return $output;
  }

  /***
   * fetch remote data from USAID ForeignAssistance.gov dataset
   *
   * @return array
   *
   */
  public function getRemoteData(): array {
    //$current_path = \Drupal::service('path.current')->getPath();
    //$path_args = explode('/', $current_path);
    //$uid = $path_args[3];


    $client = \Drupal::httpClient();

    // build query string for JSON call
    $query = 'https://data.usaid.gov/resource/azij-hu6e.json';
    $query .= '?country_name=Angola';

    try {
      $response = $client->get($query);
    } catch (RequestException $e) {
      drupal_set_message('Could not fetch data from USAID', 'error', TRUE);
      return FALSE;
    }

    // process the fetched data, store in array
    $raw = json_decode($response->getBody(), TRUE);
    $rows = [];           // array for page output
    foreach ($raw as $id => $item) {
      $country_id = $item['country_id'];
      $country_name = $item['country_name'];
      $region_name = $item['region_name'];
      $managing_agency_name = $item['managing_agency_name'];
      $us_sector_name = $item['us_sector_name'];
      $activity_name = $item['activity_name'];
      $rows[] = [$country_name, $region_name, $managing_agency_name, $us_sector_name, $activity_name];
    }

    return $rows;
  }

}
