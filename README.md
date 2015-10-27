# toggl2traffika #
toggle2traffika is a simple script to transfer your Toggl reports to Traffika.

## Toggl settings needed ##

1. Name your projects in Toggl same as they are named in Traffika
2. Create tags in Toggl corresponding to activities (development + unit test etc.)
3. When creating task in Toggl use the project names and tags for activities (only the first tag is used)

## Uploader setup ##

1. Move whole directory to safe place where you won't delete it
2. Create a copy of config.default.php named config.php and fill the needed data
3. Run `./link.sh` to link the program so you can use it anywhere

Now you can run `toggle2traffika` in terminal and all your today reports will be uploaded to Traffika.

## Reporting Summary ##
If you set up clients for projects in Toggl, you can get monthly time
summary for each client and project.

- `toggl2traffika -reporting` returns a summary for current month
- `toggl2traffika -reporting 8/2015` returns a summary for the month given

## Timesheets ##
If you want to get list of timesheets you have entered into Traffika, you can run:

- `toggl2traffika -timesheets` for the current month
- `toggl2traffika -timesheets 9/2015` for the given month

The output should be valid CSV in most cases.