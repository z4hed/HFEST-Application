const chai = require('chai');
const chaiHttp = require('chai-http');
const app = require('../index.js'); // replace with the path to your Express app

chai.use(chaiHttp);
const expect = chai.expect;
