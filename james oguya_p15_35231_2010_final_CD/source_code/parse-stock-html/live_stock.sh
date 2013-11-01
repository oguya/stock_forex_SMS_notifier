#!/bin/bash

#dl & parse the html file
python parse_html.py

#filter the parsed doc for relevant data
python filter_stock_data.py

#store the data 2 db
python db_connect.py

#move the data to liveData tbl
cd ~/workspace/forex_project/
php recordStock_Data.php


