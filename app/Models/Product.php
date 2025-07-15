<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
  protected $fillable = ['name'];

  public function orders()
  {
    return $this->hasMany(Order::class);
  }

  public static function findOrCreateByName(string $rawProductName): self
  {
    $standardizedName = self::standardizeProductName($rawProductName);
    return self::firstOrCreate(['name' => $standardizedName]);
  }

  protected static function standardizeProductName(string $productName): string
  {
    $productName = trim($productName);

    // Remove trailing quotes that appear in TWINT CSV data
    $productName = rtrim($productName, '”');

    // Define mapping from various names to standardized names
    $mappings = [
      // Single issue variants
      'DIE ERSTE AUSGABE' => 'Erste Ausgabe',
      'Die erste Ausgabe' => 'Erste Ausgabe',
      'ErsteAusgabe' => 'Erste Ausgabe',
      'Jahresabo2026' => 'Jahresabo 2026',

      // Annual subscription variants
      'JAHRESABO ERSTAUSGABE' => 'Jahresabo Erstausgabe',
      'JahresaboErstausgabe' => 'Jahresabo Erstausgabe',
      'JAHRESABO 2026' => 'Jahresabo 2026',
      'JAHRESABO' => 'Jahresabo',

      // Gift subscription
      'Geschenk-Abo' => 'Geschenk-Abo',
    ];

    return $mappings[$productName] ?? $productName;
  }
}
