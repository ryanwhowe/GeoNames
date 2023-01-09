# GeoNames
This is a project to automate the ETL of the GeoNames public data sets updates

## Usage

There are 3 primary functions of the code.

1. Download the daily updates files
2. Add to the messaging queue the updates to perform
3. Persist those updates from the queue to the database

### Initial Setup - Development 

#### Create a `docker-compose.override.yml` file

Navigate to the `<solution directory>\devops\dev` directory. And create a `docker-compose.override.yml` file from 
the example file in the source code.
```shell
cp docker-compose.override.yml.example docker-compose.override.yml
```

All that should need to be set for the development are your local paths for docker to get to the solution directory.

#### Start the dev environment

Navigate to the `<solution directory>\devops\dev` directory.

```shell
docker-compose up -d
```

#### Install the project dependencies

Navigate to the `<solution directory>\devops\dev` directory.  This only needs to be performed once when creating a 
new solution.

```shell
docker-compose exec cli composer insall
```

### Scripts

list the commands in the geonames namespace

```shell
docker-compose exec cli php ./bin/console list geonames
```