# symfony-filter-bundle

##Installation

### Setup environment
Add the bundle repository in your composer.json

```json
{
    "minimum-stability": "dev",
    "prefer-stable": true,
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/parad0xe/symfony-filter-bundle.git"
        }
    ]
}
```

Install the bundle

```bash
composer require parad0xe/symfony-filter-bundle
```

Enable the bundle by adding it to the list of registered bundles (`config/bundles.php`)

```php
Parad0xe\Bundle\FilterBundle\FilterBundle::class => ["all" => true]
```

Then, create a new directory `Filters` in `src/`

### Configuration

Create new configuration file `filter.yaml` in `config/packages`

Copy the default configuration in this file:

```yaml
filter:
    store_options:
      session_key: "__flts_sessk"
      request_key: "__flts_rqtsk"
      cleaner_key: "__flts_clnrk"
      method: "post"
      cached: true # enable filter request caching
      auto_clean_timeout: 300 # filter request cleaned after 5 minutes

    class_options:
      available_methods_prefix: "pub_" # (security) define an explicit prefix want you add on all functions available from filter request

    view_options:
      id_length: 4
      custom_case_separator: "-"
      default_render_pattern: "{id}<{method}>"
```

## Usage / Example

### Filter

Create new filter and add filter methods

`src/Filters/FooFilter.php`

```php
class FooFilter extends AbstractFilter implements FilterInterface {
    public function fromModel(): string {
        return FooEntity::class; 
    }

    public function pub_title(string $value) {
        $this->builder->andWhere("{$this->alias}.title LIKE :title")
            ->setParameter("title", "%$value%");
    }

    public function pub_minPrice(string $value) {
        $this->builder->andWhere("{$this->alias}.price >= :price")
            ->setParameter("price", $value);
    }
}
```

### Repository

Next, surround existing query with the filter

`src/Repository/FooRepository.php`

```php
class FooRepository extends ServiceEntityRepositiory {
    private $_container;

    public function __construct(ManagerRegistry $registry, ContainerInterface $container) {
        parent::__construct($registry, FooEntity::class);
        $this->_container = $container;
    }
    
    public function yourQueryMethod() {
        return FooFilter::filter($this->createQueryBuilder("your_alias"), $this->_container)
            ->getQuery()
            ->getResult();
    }
}
```

### Controller

Send the view builder in your controller (use for create associated form)

`src/Controller/FooController.php`

```php
class FooController extends AbstractController {
    public function index() {
        return $this->render('foo/index', [
            "filter" => FooFilter::view() 
        ]);
    }
}
```

### Template

Create your filter form

`templates/foo/index.html.twig`

```twig
<form action="" method="{{ filter.method }}">
    <div>
        <label>Title</label>
        <input type="text" name="{{ filter.name('title') }}" value="{{ filter.value('title') }}">
    </div>
    
    <div>
        <label>Min Price</label>
        <input type="number" name="{{ filter.name('minPrice') }}" value="{{ filter.value('minPrice') }}" min="0">
    </div>

    <button type="submit">Filter</button>
    <button type="submit" name="{{ filter.cleanButtonName }}">Clear</button>
</form>
```

Available methods:

`$associated_method_name` is the filter method name without `config.class_options.available_methods_prefix`

Retrieve input name of filter:
> getName(string $associated_method_name, $input_multiple = false): string

Retrieve input value of filter:
> getValue(string $associated_method_name): string

Get name of cleaner button:
> getCleanButtonName(): string

Check if value contain in submitted filters:
> containValue(string $value, string $associated_method_name): bool

Alias to containValue:
> isSelected(string $value, string $associated_method_name): bool

Check if value of submitted filters is empty:
> emptyValue(string $associated_method_name): bool

Return the form method used:
> getMethod(): string
