<?php
// Goat api search block fo Chicago Institute of Art

namespace Drupal\ssa\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Form\FormInterface;

/**
 * Provides a block with a simple text.
 * Used in goat node display to show parents
 *
 * @Block(
 *   id = "ssa_block",
 *   admin_label = @Translation("ssa block"),
 *   category = @Translation("farm"),
 * )
 */
class ssa_block extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $ssa_form = \Drupal::formBuilder()->getForm('Drupal\ssa\Form\SsaForm');

    return [
      '#theme' => 'ssa_block',
      '#config_form' => $ssa_form,
      '#data' => $ssa_form['output']['#text'],
    ];

  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }
}
