import React, { useContext } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { AuthContext } from '../context/AuthContext';
import { CartContext } from '../context/CartContext';

const Navbar = () => {
  const { user, logout } = useContext(AuthContext);
  const { cart } = useContext(CartContext);
  const navigate = useNavigate();

  const handleLogout = () => {
    logout();
    navigate('/');
  };

  return (
    <nav style={{ padding: '1rem', background: '#eee' }}>
      <Link to="/" style={{ marginRight: '1rem' }}>Home</Link>
      {user && user.isAdmin && <Link to="/admin" style={{ marginRight: '1rem' }}>Admin</Link>}
      <Link to="/user" style={{ marginRight: '1rem' }}>User</Link>
      <Link to="/cart" style={{ marginRight: '1rem' }}>Cart ({cart.length})</Link>
      {user ? (
        <>
          <span style={{ marginRight: '1rem' }}>Hello, {user.name}</span>
          <button onClick={handleLogout}>Logout</button>
        </>
      ) : (
        <>
          <Link to="/login" style={{ marginRight: '1rem' }}>Login</Link>
          <Link to="/register">Register</Link>
        </>
      )}
    </nav>
  );
};

export default Navbar; 