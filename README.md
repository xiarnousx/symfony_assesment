# Setting up Development environment with Docker

## Assumptions about Host Environment:

1. Host environment is a debian derivative that has the apt package manager command

### Installing Docker and Docker Compose

```bash
apt-get install docker docker-compose
```

once the above are installed, `cd` into the project directory and do `docker-compose up -d` in order to bring up the containers

### Installing the vendor folder

The vendor folder is removed and not version controlled. Before hitting the API endpoints kindly do the below command

```bash
docker-compose run php composer install
```

### Special note:

In order not to face permission proble while editing php files inside the `api` folder update the variable `LOCAL_USER` to the `uid`:`gid` of your system by running the `id` command

```bash
id
```
