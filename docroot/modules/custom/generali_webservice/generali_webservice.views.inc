<?php

/**
 * Implements hook_views_data().
 */
function generali_webservice_views_data() {
  $data = [];
  $data['generali_websalescommon_finderbroker2_data'] = [
    'table' => [
      'group' => t('Generali WebsalesCommon service - FindBroker2'),
      'provider' => 'generali_webservice',
      'base' => [
        'field' => 'broker_broker_user_number',
        'title' => t('Generali WebsalesCommon service - FindBroker2'),
        'help' => t('Generali WebsalesCommon service FindBroker2 source'),
        'query_id' => 'generali_websales_common_service_query',
      ],
    ],
    'broker_broker_user_number' => [
      'title' => t('Broker user number'),
      'field' => [
        'id' => 'standard',
      ],
    ],
    'broker_broker_user_type' => [
      'title' => t('Broker user type'),
      'field' => [
        'id' => 'standard',
      ],
    ],
    'broker_broker_name' => [
      'title' => t('Broker name'),
      'field' => [
        'id' => 'standard',
      ],
    ],
    'broker_address_address' => [
      'title' => t('Broker address'),
      'field' => [
        'id' => 'standard',
      ],
    ],
    'broker_address_street_number' => [
      'title' => t('Broker street number'),
      'field' => [
        'id' => 'standard',
      ],
    ],
    'broker_address_post_box' => [
      'title' => t('Broker post box'),
      'field' => [
        'id' => 'standard',
      ],
    ],
    'broker_locality_zip_code' => [
      'title' => t('Broker locality zip code'),
      'field' => [
        'id' => 'standard',
      ],
      'filter' => array(
        'id' => 'views_autocomplete_filters_string',
      ),
    ],
    'broker_locality_name' => [
      'title' => t('Broker locality name'),
      'field' => [
        'id' => 'standard',
      ],
      'filter' => array(
        // ID of filter handler plugin to use.
        'id' => 'string',
      ),
    ],
    'broker_address_country' => [
      'title' => t('Broker country'),
      'field' => [
        'id' => 'standard',
      ],
    ],
    'broker_coordinates_x' => [
      'title' => t('Broker x-coordinate'),
      'field' => [
        'id' => 'numeric',
      ],
    ],
    'broker_coordinates_y' => [
      'title' => t('Broker y-coordinate'),
      'field' => [
        'id' => 'numeric',
      ],
    ],
    'broker_cbfa' => [
      'title' => t('Broker CBFA number'),
      'field' => [
        'id' => 'standard',
      ],
    ],
    'broker_fsma' => [
      'title' => t('Broker FSMA number'),
      'field' => [
        'id' => 'standard',
      ],
    ],
    'broker_phone_number_1' => [
      'title' => t('Broker phone number 1'),
      'field' => [
        'id' => 'standard',
      ],
    ],
    'broker_phone_number_2' => [
      'title' => t('Broker phone number 2'),
      'field' => [
        'id' => 'standard',
      ],
    ],
    'broker_gsm' => [
      'title' => t('Broker gsm number'),
      'field' => [
        'id' => 'standard',
      ],
    ],
    'broker_fax' => [
      'title' => t('Broker fax number'),
      'field' => [
        'id' => 'standard',
      ],
    ],
    'broker_email' => [
      'title' => t('Broker email'),
      'field' => [
        'id' => 'standard',
      ],
    ],
    'broker_email_branch_lead' => [
      'title' => t('Broker email branchlead'),
      'field' => [
        'id' => 'standard',
      ],
    ],
    'broker_website_url' => [
      'title' => t('Broker website URL'),
      'field' => [
        'id' => 'standard',
      ],
    ],
    'broker_language' => [
      'title' => t('Broker language'),
      'field' => [
        'id' => 'standard',
      ],
    ],
    'broker_language_code' => [
      'title' => t('Broker language code'),
      'field' => [
        'id' => 'standard',
      ],
    ],
    'broker_brocom' => [
      'title' => t('Broker BROCOM'),
      'field' => [
        'id' => 'boolean',
      ],
    ],
    'broker_gforce' => [
      'title' => t('Broker GFORCE'),
      'field' => [
        'id' => 'boolean',
      ],
    ],
    'broker_logo_url' => [
      'title' => t('Broker logo URL'),
      'field' => [
        'id' => 'standard',
      ],
    ],
    //    'broker_inspector' => [
    //
    //    ],
    'broker_broker_stars' => [
      'title' => t('Broker stars'),
      'field' => [
        'id' => 'numeric',
      ],
    ],
    'broker_enterprise_number' => [
      'title' => t('Broker enterprise number'),
      'field' => [
        'id' => 'standard',
      ],
    ],
  ];

  return $data;
}
