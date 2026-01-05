export const bulkActions = [
  { value: 'status-open', label: 'Status offen' },
  { value: 'status-fulfilled', label: 'Status erledigt' },
  { value: 'status-cancelled', label: 'Status gekündigt' },
  { value: 'notes', label: 'Bemerkungen' },
  { value: 'export-csv', label: 'Export CSV' },
  { value: 'update-product', label: 'Produkt aktualisieren' },
  { value: 'update-subscription', label: 'Abonnement aktualisieren' },
  // { value: 'generate-labels', label: 'Etiketten generieren' }
];

export const statusOptions = [
  { value: 'open', label: 'offen' },
  { value: 'fulfilled', label: 'erledigt' },
  { value: 'cancelled', label: 'gekündigt' }
];

export const defaultSortKey = 'paid_at';
export const defaultSortDirection = 'desc';