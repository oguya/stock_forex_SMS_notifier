echo "About to start 1 operation."
cd /home/james/workspace/forex_project
echo "Done"
sleep 1;

echo "About to execute forex_info.php"
php forex_info.php
echo "Done"
sleep 1;

echo "About to execute stock_info.php"
php stock_info.php
echo "Done"
sleep 1;

echo "About to execute stock_subs.php"
php stock_subs.php
echo "Done"
sleep 1;

echo "About to execute subscriptions.php"
php subscriptions.php


