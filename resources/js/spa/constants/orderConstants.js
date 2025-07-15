export const bulkActions = [
  { value: 'status-open', label: 'Status offen' },
  { value: 'status-fulfilled', label: 'Status erledigt' },
  { value: 'notes', label: 'Bemerkungen' },
  { value: 'export-csv', label: 'Export CSV' },
  // { value: 'generate-labels', label: 'Etiketten generieren' }
];

export const statusOptions = [
  { value: 'open', label: 'offen' },
  { value: 'fulfilled', label: 'erledigt' }
];

export const defaultSortKey = 'paid_at';
export const defaultSortDirection = 'desc';