# mySQLtoes
Handy MySQL to Elasticsearch tool in PHP

mySQLtoes is the implementation of Elasticsearch PHP client as a configurable tool to select and insert mySQL databases tables into ES.

at it's current state mySQLtoes only supports flat tables. this app should be launched from command line and allows you to:

1. Insert all your mySQL table records to elasticsearch.
2. Select the column you wish to use for _id in elasticsearch.
3. Automatically create fields based on columns name in your MySQL table.
4. Batch inserting into elastic search, pagination queries. you can set a limit for each select and insert into ES.


I'll be adding more functionalities such as custom mapping and ... to it in near future if I can manage my time.

I hope it can be helpful for some of you ;)
Mahdi