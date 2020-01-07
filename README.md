# contributiontransactlegacy

Install this if you need to continue using (a fixed) version of the Contribution.transact API because your integration has not yet been updated away from it.

The implementation used in this extension is based on https://docs.civicrm.org/dev/en/latest/financial/orderAPI/#transitioning-from-contributiontransact-api-to-order-api

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.1+
* CiviCRM 5.20+

## Installation

See: https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/#installing-a-new-extension

## Usage

Just install and relax. And then sort out the legacy code that is calling Contribution.transact so you don't need this extension...
