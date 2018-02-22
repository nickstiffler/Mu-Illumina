from flask import Flask
from flask_restful import Resource, Api, reqparse
from flask_sqlalchemy import SQALchemy


app = Flask(__name__)
app.config['SQLALCHEMY_DATABASE_URI'] = 'mysql://root:420bigmoney@localhost/mu_illumina_v4'
db = SQLAlchemy(app)

# MySQL configurations
app.config['MYSQL_DATABASE_USER'] = 'root'
app.config['MYSQL_DATABASE_PASSWORD'] = '420bigmoney'
app.config['MYSQL_DATABASE_DB'] = 'mu_illumina_v4'
app.config['MYSQL_DATABASE_HOST'] = 'localhost'


mysql.init_app(app)

api = Api(app)

class Clusters(Resource):
	def post(self):
		parser = reqparse.RequestParser()
		args = parser.parse_args()

		runs = args.runs
		barcodes = args.barcodes
		maize = args.maize
		arab = args.arab
		mutant = args.mutant
		ear = args.ear

		conn = mysql.connect()
		cursor = conn.cursor()
            # Query
		data = cursor.fetchall()

class Runs(Resource):
	def get(self):
	parser = reqparse.RequestParser()
	args = parser.parse_args()

	conn = mysql.connect()
	cursor = conn.cursor()
            # Query
	data = cursor.fetchall()

class Barcodes(Resource):
	def get(self):
		parser = reqparse.RequestParser()
		args = parser.parse_args()

		conn = mysql.connect()
		cursor = conn.cursor()
            # Query
		data = cursor.fetchall()

api.add_resource(Clusters, '/Clusters')
api.add_resource(Runs, '/Runs')
api.add_resource(Barcodes, '/Barcodes')

if __name__ == '__main__':
    app.run(debug=True)

