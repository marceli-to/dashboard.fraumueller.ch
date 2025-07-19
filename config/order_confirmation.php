<?php

return [
  /*
  |--------------------------------------------------------------------------
  | Order Confirmation Templates
  |--------------------------------------------------------------------------
  |
  | This array defines the available templates for order confirmation emails.
  | Each key represents a template identifier, and each value contains the
  | product name to match against and the email subject for that product.
  |
  */

  'templates' => [
      'geschenk-abo' => [
        'name' => 'Geschenk-Abo',
        'subject' => 'Twint Geschenkabo - fraumueller.ch'
      ],
      'jahresabo-2026' => [
        'name' => 'Jahresabo 2026',
        'subject' => 'Jahresabo 2026 - fraumueller.ch'
      ],
      'erste-ausgabe' => [
        'name' => 'Erste Ausgabe',
        'subject' => 'Erstausgabe - fraumueller.ch'
      ],
      'jahresabo-erstausgabe' => [
        'name' => 'Jahresabo Erstausgabe',
        'subject' => 'Jahresabo Erstausgabe - fraumueller.ch'
      ],
      'trikot' => [
        'name' => 'Original Frau Müller Trikot',
        'subject' => 'Original Frau Müller Trikot - fraumueller.ch'
      ],
      'trikot-xs-s' => [
        'name' => 'Trikot_XS/S',
        'subject' => 'Original Frau Müller Trikot - fraumueller.ch'
      ],
      'trikot-m-l' => [
        'name' => 'Trikot_M/L',
        'subject' => 'Original Frau Müller Trikot - fraumueller.ch'
      ],
  ],

  /*
  |--------------------------------------------------------------------------
  | Default Subject
  |--------------------------------------------------------------------------
  |
  | The default subject line to use when no specific template is found
  | or when the product doesn't match any configured templates.
  |
  */

  'default_subject' => 'Bestellbestätigung fraumueller.ch'
];