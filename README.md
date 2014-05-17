giffgaffScrape
==============

a simple script to scrape the giffgaff site

To use put this script in a directory on a webserver running php.

Access that directory from a web browser, you should get an account error message.
To use provide the script GET parameters in the format of "dir/?nickname={your nickname}&password={your password}",
this will give you all of your data back in the form of a php array.

```

Array
(
    [Balance] => 8.79
    [Phone Number] => 07712345678
    [Payback] => Array
        (
            [Points] => 710
            [Value] => 7.1
        )

    [CurrentGoodybag] => Array
        (
            [Dates] => Array
                (
                    [End] => 06 Jun
                )

            [Minutes] => Array
                (
                    [Amount] => 375
                    [Percent Free] => 75
                )

            [Texts] => Array
                (
                    [Amount] => Unlimited
                    [Percent Free] => 100
                )

            [Data] => Array
                (
                    [Amount] => 772.3 MB
                    [Percent Free] => 75.419807434082
                )

        )

    [NextGoodybag] => Array
        (
            [Dates] => Array
                (
                    [Start] => 07 Jun
                    [End] => 06 Jul
                )

            [Minutes] => Array
                (
                    [Amount] => 500
                    [Percent Free] => 100
                )

            [Texts] => Array
                (
                    [Amount] => Unlimited
                    [Percent Free] => 100
                )

            [Data] => Array
                (
                    [Amount] => 1 GB
                    [Percent Free] => 100
                )

        )

)


```

To retrieve data in a json format, simply modify the GET parameters to be:

"http://example.com/dir/?nickname={your nickname}&password={your password}&format=json"

```
{
  "Balance": 8.79,
  "Phone Number": "07712345678",
  "Payback": {
    "Points": 710,
    "Value": 7.1
  },
  "CurrentGoodybag": {
    "Dates": {
      "End": "06 Jun"
    },
    "Minutes": {
      "Amount": 375,
      "Percent Free": 75
    },
    "Texts": {
      "Amount": "Unlimited",
      "Percent Free": 100
    },
    "Data": {
      "Amount": "772.3 MB",
      "Percent Free": 75.419807434082
    }
  },
  "NextGoodybag": {
    "Dates": {
      "Start": "07 Jun",
      "End": "06 Jul"
    },
    "Minutes": {
      "Amount": 500,
      "Percent Free": 100
    },
    "Texts": {
      "Amount": "Unlimited",
      "Percent Free": 100
    },
    "Data": {
      "Amount": "1 GB",
      "Percent Free": 100
    }
  }
}
```

For XML:

"http://example.com/dir/?nickname={your nickname}&password={your password}&format=xml"

```
<?xml version="1.0" encoding="UTF-8"?>
<giffgaff_data>
   <Balance>8.79</Balance>
   <Phone_Number>07712345678</Phone_Number>
   <Payback>
      <Points>710</Points>
      <Value>7.1</Value>
   </Payback>
   <CurrentGoodybag>
      <Dates>
         <End>06 Jun</End>
      </Dates>
      <Minutes>
         <Amount>375</Amount>
         <Percent_Free>75</Percent_Free>
      </Minutes>
      <Texts>
         <Amount>Unlimited</Amount>
         <Percent_Free>100</Percent_Free>
      </Texts>
      <Data>
         <Amount>772.3 MB</Amount>
         <Percent_Free>75.419807434082</Percent_Free>
      </Data>
   </CurrentGoodybag>
   <NextGoodybag>
      <Dates>
         <Start>07 Jun</Start>
         <End>06 Jul</End>
      </Dates>
      <Minutes>
         <Amount>500</Amount>
         <Percent_Free>100</Percent_Free>
      </Minutes>
      <Texts>
         <Amount>Unlimited</Amount>
         <Percent_Free>100</Percent_Free>
      </Texts>
      <Data>
         <Amount>1 GB</Amount>
         <Percent_Free>100</Percent_Free>
      </Data>
   </NextGoodybag>
</giffgaff_data>
```

For use with the universal widget app (https://play.google.com/store/apps/details?id=uk.cdev.universalwidget.v1) simply use:

"http://example.com/dir/?nickname={your nickname}&password={your password}&format=uwidget"

as the url for the widget.

```
{
  "title": "giffgaff",
  "type": "list",
  "date": "07712345678 stats",
  "data": [
    {
      "name": "Minutes",
      "value": "375:75%"
    },
    {
      "name": "Texts",
      "value": "Unlimited:100%"
    },
    {
      "name": "Data MB",
      "value": "772.3:75.4%"
    }
  ]
}
```

all source code is badly commented, use it as you want.
