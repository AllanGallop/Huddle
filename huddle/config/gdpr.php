<?php

return [

  /*
  |--------------------------------------------------------------------------
  | Data controller
  |--------------------------------------------------------------------------
  |
  | Contact details shown on the privacy policy page.
  |
  */

  'controller_name' => env('GDPR_CONTROLLER_NAME', config('app.name')),

  'contact_email' => env('GDPR_CONTACT_EMAIL'),

  /*
  |--------------------------------------------------------------------------
  | Privacy policy
  |--------------------------------------------------------------------------
  */

  'policy_version' => env('GDPR_POLICY_VERSION', '1.0'),

  /*
  |--------------------------------------------------------------------------
  | Erasure placeholder account
  |--------------------------------------------------------------------------
  |
  | Historical records that must keep a user reference are reassigned to this
  | non-loginable account when a member is erased.
  |
  */

  'placeholder_email' => env('GDPR_PLACEHOLDER_EMAIL', 'gdpr-placeholder@invalid'),

  'placeholder_name' => env('GDPR_PLACEHOLDER_NAME', 'Deleted member'),

];
