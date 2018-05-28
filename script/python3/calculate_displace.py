import numpy as np
import psycopg2 as pg 
import psycopg2.extras

connection = pg.connect("host=kona.telepath port=5432 dbname=shamen3 user=postgres")

cursor = connection.cursor(cursor_factory=psycopg2.extras.DictCursor)
cursor.execute("SELECT * FROM spots")

results = cursor.fetchall()
values = []
for row in results:
    values.append(dict(row))

print(values[0])

cursor.close()
connection.close()

# import matplotlib
# matplotlib.use('Agg')
# import matplotlib.pyplot as plt

#x = np.arange(0, 5, 0.1)
#y = np.sin(x)
#plt.plot(x, y)
#plt.show()
#print (x)
#print (y)