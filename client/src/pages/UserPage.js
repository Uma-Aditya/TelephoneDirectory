import React, { useEffect, useState, useContext } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { CartContext } from '../context/CartContext';

const UserPage = () => {
  const [products, setProducts] = useState([]);
  const [search, setSearch] = useState('');
  const [category, setCategory] = useState('');
  const { addToCart } = useContext(CartContext);

  useEffect(() => {
    axios.get('/api/admin/products')
      .then(res => setProducts(res.data))
      .catch(err => console.error(err));
  }, []);

  // Extract unique categories from products
  const categories = Array.from(new Set(products.map(p => p.category).filter(Boolean)));

  // Filter products by search and category
  const filteredProducts = products.filter(product => {
    const matchesSearch = product.name.toLowerCase().includes(search.toLowerCase());
    const matchesCategory = category ? product.category === category : true;
    return matchesSearch && matchesCategory;
  });

  return (
    <div>
      <h1>Welcome to ShopSmart</h1>
      <p>Your Digital Grocery Store Experience</p>
      <img src="/images/grocery-store.jpg" alt="Grocery Store" style={{maxWidth: '100%', height: 'auto'}} />
      <div style={{ margin: '2rem 0' }}>
        <Link to="/login">Login</Link> | <Link to="/register">Register</Link>
      </div>
      <div style={{ marginBottom: '1rem' }}>
        <input
          type="text"
          placeholder="Search products..."
          value={search}
          onChange={e => setSearch(e.target.value)}
          style={{ marginRight: '1rem' }}
        />
        <select value={category} onChange={e => setCategory(e.target.value)}>
          <option value="">All Categories</option>
          {categories.map(cat => (
            <option key={cat} value={cat}>{cat}</option>
          ))}
        </select>
      </div>
      <h2>Products</h2>
      <div style={{ display: 'flex', flexWrap: 'wrap', gap: '2rem' }}>
        {filteredProducts.map(product => (
          <div key={product._id} style={{ border: '1px solid #ccc', padding: '1rem', width: 250 }}>
            <img src={product.image || '/images/grocery-store.jpg'} alt={product.name} style={{ width: '100%', height: 120, objectFit: 'cover' }} />
            <h3>{product.name}</h3>
            <p>{product.description}</p>
            <p><b>${product.price}</b></p>
            {product.category && <p>Category: {product.category}</p>}
            <button onClick={() => addToCart(product)}>Add to Cart</button>
          </div>
        ))}
      </div>
    </div>
  );
};

export default UserPage; 