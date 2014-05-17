giffgaffScrape
==============

a simple script to scrape the giffgaff site

To use put this script in a directory on a webserver running php.

Access that directory from a web browser, you should get an account error message.
To use provide the script GET parameters in the format of "dir/?nickname={your nickname}&password={your password}",
this will give you all of your data back in the form of a php array.

To retrieve data in a json format, simply modify the GET parameters to be:

"http://example.com/dir/?nickname={your nickname}&password={your password}&format=json"
For XML:

"http://example.com/dir/?nickname={your nickname}&password={your password}&format=xml"

For use with the universal widget app simply use:

"http://example.com/dir/?nickname={your nickname}&password={your password}&format=uwidget"
as the url for the widget.

all source code is badly commented, use it as you want.
