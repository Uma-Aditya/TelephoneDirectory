import React, { useContext } from 'react';
import { CartContext } from '../context/CartContext';

const CartPage = () => {
  const { cart, removeFromCart } = useContext(CartContext);

  const total = cart.reduce((sum, item) => sum + Number(item.price), 0);

  return (
    <div>
      <h2>Your Cart</h2>
      {cart.length === 0 ? (
        <p>Your cart is empty.</p>
      ) : (
        <>
          <ul>
            {cart.map((item, idx) => (
              <li key={item._id + idx} style={{ marginBottom: '1rem' }}>
                <b>{item.name}</b> - ${item.price}
                <button style={{ marginLeft: 10 }} onClick={() => removeFromCart(item._id)}>Remove</button>
              </li>
            ))}
          </ul>
          <h3>Total: ${total.toFixed(2)}</h3>
          <button disabled>Checkout (Demo)</button>
        </>
      )}
    </div>
  );
};

export default CartPage; 