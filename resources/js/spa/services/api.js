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

