# myhermescsv
with a combination of the myhermes email and the csv provided to myhermes, 
generate output of order reference and shipping reference for import into your favourite application.  
Originally created to solve the problem of re-importing data from Myhermes into ecommerce applications 
that output the myhermes csv file format.

No installation required, just: 
Upload the files to your web server
Submit the html format email supplied by myhermes after you ship your item
and the csv file that you supply to Myhermes to generate shipments
Click submit and a csv file will be generated. 

Notes: 

A csv file will only be generated if the number of shipments exactly match and their respective postcodes approximately match
errors will display differences so you can figure out what went wrong.  remember to use it for each completed mass shipment. 

The application will read and convert badly formatted postcodes to good ones. 

Very simple, uses simple html dom to read the html file and extract the relevant data 
http://sourceforge.net/projects/simplehtmldom/

Also uses a nifty postcode validator to check postcodes: 
http://www.braemoor.co.uk/software/postcodes.shtml


