const express = require('express');
const router = express.Router();
const productController = require('../controllers/productController');
const auth = require('../middleware/auth');

router.use(auth);

// Product CRUD
router.get('/products', productController.getProducts);
router.post('/products', productController.createProduct);
router.put('/products/:id', productController.updateProduct);
router.delete('/products/:id', productController.deleteProduct);

// Example admin route
router.get('/', (req, res) => {
  res.json({ message: 'Admin route' });
});

module.exports = router; 