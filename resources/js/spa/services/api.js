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

// csv processing
export const processCsv = async (filePath, merchant) => {
  const response = await api.post(`/upload/process`, { file_path: filePath, merchant: merchant });
  return response.data;
}; 

