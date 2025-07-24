const express = require('express');
const mongoose = require('mongoose');
const cors = require('cors');
require('dotenv').config();

const app = express();
const corsOptions = {
  origin: 'http://localhost:3000',
  credentials: true,
};
app.use(cors(corsOptions));
app.use(express.json());

// Placeholder routes
app.get('/', (req, res) => res.send('API Running'));
app.use('/api/admin', require('./routes/admin'));
app.use('/api/user', require('./routes/user'));

const PORT = process.env.PORT || 5000;

mongoose.connect(process.env.MONGO_URI || 'mongodb://localhost:27017/shopez', {
  useNewUrlParser: true,
  useUnifiedTopology: true,
})
.then(() => {
  app.listen(PORT, () => console.log(`Server started on port ${PORT}`));
})
.catch((err) => console.error('MongoDB connection error:', err)); 