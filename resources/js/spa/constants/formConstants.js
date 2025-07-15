export const paymentMethodOptions = [
  { value: 'twint', label: 'TWINT' },
  { value: 'invoice', label: 'Rechnung' },
  { value: 'creditcard', label: 'Kreditkarte' }
];

export const merchantOptions = [
  { value: 'twint', label: 'TWINT' },
  { value: 'squarespace', label: 'Squarespace' },
  { value: 'other', label: 'Andere' }
];

export const defaultFormData = {
  product_id: '',
  email: '',
  phone: '',
  payment_method: '',
  merchant: '',
  total: '',
  paid_at: '',
  billing_name: '',
  billing_address_1: '',
  billing_address_2: '',
  billing_city: '',
  billing_zip: '',
  billing_country: '',
  shipping_name: '',
  shipping_address_1: '',
  shipping_address_2: '',
  shipping_city: '',
  shipping_zip: '',
  shipping_province: '',
  shipping_country: '',
  notes: ''
};

export const updateFormFields = [
  'product_id',
  'email', 
  'phone',
  'billing_name',
  'billing_address_1',
  'billing_address_2',
  'billing_city',
  'billing_zip',
  'billing_country',
  'shipping_name',
  'shipping_address_1',
  'shipping_address_2',
  'shipping_city',
  'shipping_zip',
  'shipping_province',
  'shipping_country',
  'notes'
];