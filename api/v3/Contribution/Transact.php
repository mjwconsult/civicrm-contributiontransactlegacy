<?php
/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

/**
 * @package CiviCRM_APIv3
 */

/**
 * Adjust Metadata for Transact action.
 *
 * The metadata is used for setting defaults, documentation & validation.
 *
 * @param array $params
 *   Array of parameters determined by getfields.
 */
function _civicrm_api3_contribution_transact_spec(&$params) {
  $fields = civicrm_api3('Contribution', 'getfields', ['action' => 'create']);
  $params = array_merge($params, $fields['values']);
  $params['receive_date']['api.default'] = 'now';
}

/**
 * Process a transaction and record it against the contact.
 *
 * @deprecated
 *
 * @param array $params
 *   Input parameters.
 *
 * @return array
 *   contribution of created or updated record (or a civicrm error)
 */
function civicrm_api3_contribution_transact($params) {
  CRM_Core_Error::deprecatedFunctionWarning('The contibution.transact api is unsupported.
    You are using the contributiontransactlegacy extension to work around this - please see the section
    at the bottom of https://docs.civicrm.org/dev/en/latest/financial/OrderAPI/ to fix properly.');

  // Start with the same parameters as Contribution.transact.

  // It would be better just to include the relevant params but....
  $paymentParams = $params;

  $params['contribution_status_id'] = 'Pending';
  if (!isset($params['invoice_id'])) {
    // Set an invoice_id here if you have not already done so.
    // Potentially Order api should do this https://lab.civicrm.org/dev/financial/issues/78
  }
  if (!isset($params['invoiceID'])) {
    // This would be required prior to https://lab.civicrm.org/dev/financial/issues/77
    $params['invoiceID'] = $params['invoice_id'];
  }
  $order = civicrm_api3('Order', 'create', $params);
  try {
    // Use the Payment Processor to attempt to take the actual payment. You may
    // pass in other params here, too.
    civicrm_api3('PaymentProcessor', 'pay', [
      'payment_processor_id' => $params['payment_processor_id'],
      'contribution_id' => $order['id'],
      'amount' => $params['total_amount'],
    ]);

    // Assuming the payment was taken, record it which will mark the Contribution
    // as Completed and update related entities.
    civicrm_api3('Payment', 'create', [
      'contribution_id' => $order['id'],
      'total_amount' => $params['amount'],
      'payment_instrument_id' => $params['payment_instrument_id'],
      // If there is a processor, provide it:
      'payment_processor_id' => $params['payment_processor_id'],
    ]);
  }
  catch (Exception $e) {
    return ['error_message' => $e->getMessage()];
  }

  return civicrm_api3('Contribution', 'getsingle', ['id' => $order['id']]);
}
