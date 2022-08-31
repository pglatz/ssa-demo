<?php
/**
 * @file
 * Contains \Drupal\ssa\Form\ChiForm.
 */
namespace Drupal\ssa\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Render\Markup;


class SsaForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ssa_form';
  }
  /**
   * @inheritdoc
   */
  protected function getEditableConfigNames() {
    return ['ssa.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // list of countries - this should be replaced by a dynamic query
    $countries = [
      'Afghanistan' => 'Afghanistan',
      'Angola' => 'Angola',
      'Armenia' => 'Armenia',
      'Belarus' => 'Belarus',
      'Cameroon' => 'Cameroon',
      'Haiti' => 'Haiti',
      'Mali' => 'Mali',
      'Nepal' => 'Nepal',
      'Senegal' => 'Senegal',
      'Somalia' => 'Somalia',
    ];

    $form['#cache'] = [
      'max-age' => 0,   // prevent caching
    ];

    $config = $this->config('ssa.settings');
    $form['country_name'] = array(
      '#type' => 'select',
      '#title' => t('Country'),
      '#required' => false,
      '#options' => $countries,
      '#default_value' => $config->get('chi_country_name') ?? 5,
    );

    $form['output'] = array(
      '#type' => 'value',
      '#text' => $config->get('form_table_data') ?? '',
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->config('ssa.settings');
    $country_name = $form_state->getValue('country_name');
    $config->set('chi_country_name', $country_name);

    // fetch = data
    $fetched_data = $this->getRemoteData($country_name);

    // build table of output
    $header = [
      'col1' => t('Country'),
      'col2' => t('Region'),
      'col3' => t('Managing Agency'),
      'col4' => t('Constant Dollar Amt'),
      'col5' => t('Sector'),
      'col6' => t('Activity'),
    ];

    $rows = [];           // array for page output

    foreach ($fetched_data as $id => $item) {
      $country_id = $item['country_id'];
      $constant_dollar_amount = $item['constant_dollar_amount'];
      $country_name = $item['country_name'];
      $region_name = $item['region_name'];
      $managing_agency_name = $item['managing_agency_name'];
      $us_sector_name = $item['us_sector_name'];
      $activity_name = $item['activity_name'];
      $rows[] = [$country_name, $region_name, $managing_agency_name, $constant_dollar_amount, $us_sector_name, $activity_name];
    }



    $table = array(
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => t('No programs could be found.'),
    );

    $render_service = \Drupal::service('renderer');
    $table_output = $render_service->renderPlain($table);

    $config->set('form_table_data', $table_output);
    $config->save();

    return;
  }


  /***
   * fetch remote data from USAID ForeignAssistance.gov dataset
   *
   * @return array
   *
   */
  public function getRemoteData($country_name = null): array {
    //$current_path = \Drupal::service('path.current')->getPath();
    //$path_args = explode('/', $current_path);
    //$uid = $path_args[3];

    $query_limit = 100; // optional query limit
    $client = \Drupal::httpClient();

    // build query string for JSON call
    $query = 'https://data.usaid.gov/resource/azij-hu6e.json?$limit=' . $query_limit;
    if (isset($country_name)) {
      // add optional passed argument
      $query .= '&country_name=' . $country_name;
    }

    try {
      $response = $client->get($query);
    } catch (RequestException $e) {
      drupal_set_message('Could not fetch data from USAID', 'error', TRUE);
      return FALSE;
    }

    // process the fetched data, store in array
    $raw = json_decode($response->getBody(), TRUE);

    return $raw;
  }


}
