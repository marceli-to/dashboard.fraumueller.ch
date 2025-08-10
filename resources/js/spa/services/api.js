import api from '@/services/axios'

// votes
export const getVotes = async () => {
  const response = await api.get(`/votes`);
  return response.data;
};

// comments
export const getComments = async () => {
  const response = await api.get(`/comments`);
  return response.data;
};

// delete comment
export const deleteComment = async (id) => {
  const response = await api.delete(`/comments/${id}`);
  return response.data;
};

// hide comment
export const toggleComment = async (id) => {
  const response = await api.put(`/comments/toggle/${id}`);
  return response.data;
};

// update comment
export const updateComment = async (id, comment) => {
  const response = await api.put(`/comments/update/${id}`, comment);
  return response.data;
};

// restore comment
export const restoreComment = async (id) => {
  const response = await api.put(`/comments/restore/${id}`);
  return response.data;
};

// orders
export const getOrders = async () => {
  const response = await api.get(`/orders`);
  return response.data;
};

export const getOrder = async (id) => {
  const response = await api.get(`/orders/${id}`);
  return response.data;
};

export const updateOrder = async (id, orderData) => {
  const response = await api.put(`/orders/${id}`, orderData);
  return response.data;
};

export const createOrder = async (orderData) => {
  const response = await api.post('/orders', orderData);
  return response.data;
};

export const bulkUpdateOrders = async (orderIds, updateData) => {
  const response = await api.post('/orders/bulk-update', {
    order_ids: orderIds,
    ...updateData
  });
  return response.data;
};

export const deleteOrder = async (id) => {
  const response = await api.delete(`/orders/${id}`);
  return response.data;
};

export const exportOrdersCsv = async (orderIds) => {
  const response = await api.post('/export/orders/csv', {
    order_ids: orderIds
  });
  return response.data;
};

export const getProducts = async () => {
  const response = await api.get('/products');
  return response.data;
};

export const getProduct = async (id) => {
  const response = await api.get(`/products/${id}`);
  return response.data;
};

export const createProduct = async (productData) => {
  const response = await api.post('/products', productData);
  return response.data;
};

export const updateProduct = async (id, productData) => {
  const response = await api.put(`/products/${id}`, productData);
  return response.data;
};

export const deleteProduct = async (id) => {
  const response = await api.delete(`/products/${id}`);
  return response.data;
};

export const sendTestNotification = async (id) => {
  const response = await api.post(`/products/${id}/send-test-notification`);
  return response.data;
};

export const getOrderLogs = async () => {
  const response = await api.get('/order-logs');
  return response.data;
};


// csv processing
export const processCsv = async (filePath, merchant) => {
  const response = await api.post(`/upload/process`, { file_path: filePath, merchant: merchant });
  return response.data;
}; 

