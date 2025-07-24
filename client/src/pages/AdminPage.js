import React, { useEffect, useState } from 'react';
import axios from 'axios';

const AdminPage = () => {
  const [products, setProducts] = useState([]);
  const [form, setForm] = useState({ name: '', description: '', price: '', image: '', stock: '', category: '' });
  const [editId, setEditId] = useState(null);

  const fetchProducts = () => {
    axios.get('/api/admin/products')
      .then(res => setProducts(res.data))
      .catch(err => console.error(err));
  };

  useEffect(() => {
    fetchProducts();
  }, []);

  const handleChange = e => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  const handleSubmit = e => {
    e.preventDefault();
    if (editId) {
      axios.put(`/api/admin/products/${editId}`, form)
        .then(() => { setForm({ name: '', description: '', price: '', image: '', stock: '', category: '' }); setEditId(null); fetchProducts(); })
        .catch(err => alert(err.response?.data?.error || 'Error'));
    } else {
      axios.post('/api/admin/products', form)
        .then(() => { setForm({ name: '', description: '', price: '', image: '', stock: '', category: '' }); fetchProducts(); })
        .catch(err => alert(err.response?.data?.error || 'Error'));
    }
  };

  const handleEdit = product => {
    setForm(product);
    setEditId(product._id);
  };

  const handleDelete = id => {
    if (window.confirm('Delete this product?')) {
      axios.delete(`/api/admin/products/${id}`)
        .then(fetchProducts)
        .catch(err => alert(err.response?.data?.error || 'Error'));
    }
  };

  return (
    <div>
      <h1>Admin Dashboard</h1>
      <form onSubmit={handleSubmit} style={{ marginBottom: '2rem' }}>
        <input name="name" placeholder="Name" value={form.name} onChange={handleChange} required />
        <input name="description" placeholder="Description" value={form.description} onChange={handleChange} />
        <input name="price" type="number" placeholder="Price" value={form.price} onChange={handleChange} required />
        <input name="image" placeholder="Image URL" value={form.image} onChange={handleChange} />
        <input name="stock" type="number" placeholder="Stock" value={form.stock} onChange={handleChange} />
        <input name="category" placeholder="Category" value={form.category} onChange={handleChange} required />
        <button type="submit">{editId ? 'Update' : 'Add'} Product</button>
        {editId && <button type="button" onClick={() => { setForm({ name: '', description: '', price: '', image: '', stock: '', category: '' }); setEditId(null); }}>Cancel</button>}
      </form>
      <h2>Products</h2>
      <table border="1" cellPadding="8" style={{ width: '100%', maxWidth: 900 }}>
        <thead>
          <tr>
            <th>Name</th><th>Description</th><th>Price</th><th>Stock</th><th>Category</th><th>Image</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          {products.map(product => (
            <tr key={product._id}>
              <td>{product.name}</td>
              <td>{product.description}</td>
              <td>${product.price}</td>
              <td>{product.stock}</td>
              <td>{product.category}</td>
              <td><img src={product.image || '/images/grocery-store.jpg'} alt={product.name} style={{ width: 50, height: 30, objectFit: 'cover' }} /></td>
              <td>
                <button onClick={() => handleEdit(product)}>Edit</button>
                <button onClick={() => handleDelete(product._id)}>Delete</button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};

export default AdminPage; 