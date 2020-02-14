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
docker-compose exec php /bin/bash
COMPOSER_MEMORY_LIMIT=-1 composer install
```

### Special note:

In order not to face permission proble while editing php files inside the `api` folder update the variable `LOCAL_USER` to the `uid`:`gid` of your system by running the `id` command

```bash
id
```

# Assessment Requirements

Consider we have an eCommerce system where the user can manage Products and can assign tags to each product.

1. a Product has a name, image, price, and list of tags.
2. a Tag has a name.

**Create a project using symfony 3.4 and build the following APIs:**

- Create/Update Product
- List Products
- Create/Update Tag
- List Tags
- Assign Tags to a product.
- Search products by tag.

**Extra Items:**

- Pagination to Both Products and Tags

**Things I Would Consider Adding as Part of Learning Symfony Framework:**

- Sorting and Filtering on Tags and Products
- Paginiation on Search Products by tag names
- Enabling Authentication and Restrict Resources Acess

# Step By Step to Get Up and Running:

1. Clone the repository:

```bash
    git clone https://github.com/xiarnousx/symfony_assesment.git .
```

2. Run Docker Compose Command:

Assumption is made that docker and docker-compose are installed on target machine.

```bash
    cd symfony_assesment
    docker-compose up -d
```

3. Install Symfony 3.4 and Other Project Dependencies via composer:

```bash
    cd symfony_assessment
    docker-compose exec php /bin/bash
    # now inside the container run below command
    COMPOSER_MEMORY_LIMIT=-1 composer install
```

4. Load Fake data into the system using fixtures:

For this purpose I have created command inside composer.json that does the below:

- migrate the data using doctrine migration bundle
- drop the schema
- create the schema
- load fake data into the system

To run the `reset-db` command do the below

```bash
    cd symfony_assessment
    docker-compose exec php bin/console doctrine:migrations:diff
    docker-compose exec php bin/console doctrine:migrations:migrate
    docker-compose exec php /bin/console reset-db
```

5. Import Postman Collection:

At the root directory of `symfony_assessment` folder there is a file `SymfonyAssessment.postman_collection.json` which has a collection of used end points.

You can download PostMan at the below URL:

- [PostMan](https://www.postman.com/)

# API End Point Explained:

The aforementioned postman collection has the below endpoints:

**Product Related Endpoints:**

- List Products : Lists all Products With Paginiation.
- Add New Product : Creates new product. **Special Note** the endpint has response header `Location` that contains link to upload an image using `PUT`.
- Upload Product Image: Attach an image to a product. **Special Note** use `PUT` and `binary` payload using postman.
- Update Product: Patch update a product.

**Tag Related Endpoints:**

- List Tags: List all Tags With Paginiation.
- Add New Tag: Adds new tag.
- Update Tag: Patch update a tag.

**Product Tags Association EndPoints:**

- Associate Product With Tags: A post request that accept tags id array payload.

**Search for Products by Tag Name Endponts:**

- Search Products By Tag Name: A get request that has array tags query parameters with tag names.
