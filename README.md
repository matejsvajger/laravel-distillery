<p align="center"><img width="390" src="resources/images/distillery-logo.svg"></p>

<p align="center">
<a href="https://packagist.org/packages/matejsvajger/laravel-distillery"><img src="https://poser.pugx.org/matejsvajger/laravel-distillery/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/matejsvajger/laravel-distillery"><img src="https://poser.pugx.org/matejsvajger/laravel-distillery/license.svg" alt="License"></a>
</p>

## Introduction

Laravel Distillery provides an elegant way for filtering and paginating Eloquent Models. Distillery taps into native Laravel API Resource Collections and builds a paginated filtered result of models/resources while making it possible to use the Laravel's pagination templates.

## Installation & Configuration

You may use Composer to install Distillery into your Laravel project:

```sh
 composer require matejsvajger/laravel-distillery
```

After installing Distillery, publish its config using the `vendor:publish` Artisan command.

```sh
php artisan vendor:publish --tag=distillery-config
```

After publishing Distillery's config, its configuration file will be located at `config/distillery.php`. This configuration file allows you to configure your application setup options and each configuration option includes a description of its purpose, so be sure to thoroughly explore this file.

## Quickstart

Let's say you have a long list of products on route: `/product-list` all you need to do is attach `Distillable` trait to your Product model:

```php
namespace App\Models

use Illuminate\Database\Eloquent\Model;
use matejsvajger\Distillery\Traits\Distillable;

class Product extends Model {
    use Distillable;
    
    protected $distillery = [
        'hidden' => [
            //
        ],
        'default' => [
            //
        ]
    ];
	
	...
}
```

Distillable trait adds a `static function distill($filters = null)`. In your controller that handles the `/product-list` route just replace your Product model fetch call (ie:`Product:all()`) with `::distill()`:

```php
class ProductListController extends Controller
{
	public function index()
	{		
		return view('product.list',[
			'products' => Product::distill()
		]);
	}
}
```

### Pagination

By default you will get a paginated response of 15 items. This is the default Eloquent model value on `$perPage` property. You can adjust it by overwriting the value in your model or set a default value for `limit` in [distillery model property](#default-filter-values-per-model).

To add pagination links to the view call `$products->links();` in your blade template:

```blade
...
	<!-- Your existing list -->
	@foreach($products as $product)
		<tr>
			<td>{{ $product->id }}</td>
			<td>{{ $product->name }}</td>
			<td>{{ $product->description }}</td>
			<td>{{ $product->price }}</td>
		</tr>
	@endforeach
...
	<div class="text-center">
		{{ $products->links() }} <!-- Add this. -->
	</div>
...
```

There you have it, a paginated list of Product models.

_What!? This is just like Laravels' Paginator! Right, we'll want to filter it too? Ok, carry on._

### Filtering

Distillery comes with an Artisan generator command that scaffolds your filter classes for existing models. Signature has two required parameters: `'distillery:filter {filter} {model}'`

For example if we want to filter the above product list with a search query on `name` and `description` we'll need a search filter for product model. Let's create it:

```sh
php artisan distillery:filter Search Product
```

This will scaffold a Search filter in `app/Filters/Product/Search.php`

The filter class implements one method: `apply(Builder $builder, $value)`, that receives Eloquent builder and the filter value. By default the generated class returns `$builder` without modifications. You'll need write the logic yourself.

For the above Search example we would do something like this:

```php
namespace App\Filters\Product;

use Illuminate\Database\Eloquent\Builder;
use matejsvajger\Distillery\Contracts\Filter;

class Search implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        return $builder->where(function ($query) use ($value) {
            $query
                ->where('name', 'like', "{$value}%")
                ->orWhere('description', 'like', "%{$value}%");
        });
    }
}

```

Then to apply the filter to the previous product list you would just add a search query string parameter to the url:

`/product-list?search=socks` and the paginated collection will be automatically filtered and pagination links will reflect the set filters.

For more examples on filters check the [**Examples**](#examples) section.

## How it works

The idea behind Distillery is that every request parameter is a filter name/value pair. Distillery loops through all request parameters, checks if the filter for selected model exists and builds a filtered resource collection based on their values.

By default Distillery predicts that you have:

- models stored in `app/Models`,
- resources stored in `app/Http/Resources`,
- and that filters will be in `app/Filters`.

All values are configurable through the config file.

Filter names

- _page_ and
- _limit_

are **reserved** for laravel paginator.

<hr>

## Digging deeper

### Serverside filter values

Sometimes you'll want to attach additional filters on server-side. By default you don't need to pass any filters in. Distilliery will pull them out of request. Usually you'll have a seo route, that already should return a fraction of models instead of all; like a category route for products: `/category/summer-clothing?search=bikini`.

Normally you wouldn't want to pass the category id in paramaters since it's already defined with a seo slug.

You can add aditional filters not defined in URI or overwrite those by passing an array into distill function. _i.e.:_ If you have a Category filter that accepts an id, attach it in your controller:

```php
public function list(Request $request, Category $category)
{
    return view('product.list',[
        'products' => Product::distill([
            'category' => $category->id,
        ]);
    ]);	
}
```

### Distillery Facade
Distillery comes with a facade that gives you an option to distill any model without the Distillable trait.
It takes two parameters, Model FQN and filter array.

```php
Distillery::distill(Product::class, $filters);
```

### Model Resources
If you're using Distillery as API endpoints you probabbly don't want to expose your whole model to the world or maybe you want to attach some additional data. Distillery checks if [Eloquent resources](https://laravel.com/docs/5.7/eloquent-resources) exist and maps the filtered collection to them, otherwise it returns normal models.

If you don't have them just create them with Artisan

```sh
php artisan make:resource Product
```

and check out the [docs](https://laravel.com/docs/5.7/eloquent-resources#writing-resources) on how to use them.

### Default filter values per model
It is possible to define default filter values per model. For example if you want a default filter value for some model you can do it with a 'default' key in a  `protected $distillery` array on the model itself:

```php
class User extends Model {
    protected $distillery = [
        'default' => [
            'sort' => 'updated_at-desc'
        ]
    ];
}
```

### Hide filters from URI QueryString
There is a `'hidden'` config array available on model to hide filters from URI when those are applied serverside:

```php
class User extends Model {
    protected $distillery = [
        'hidden' => [
            'category' // - applied in controller; set from seo url
        ]
    ];
}
```

### API Filter-Pagination Route
_Distillery comes with a standard filtering route, where you can filter/paginate any model automatically without attaching traits to models._

This functionality **is disabled** by default. You need to enable it in the config.

Default route for filtering models is `/distill` use it in combination with model name and query string:

`/distill/{model}?page=1&limit=10&search=socks`

Models filterable by this route need to be added to the `distillery.routing.models` config array:

```php
    'routing' => [

        'enabled' => true,

        'path' => 'distill',

        'middleware' => [
            'web',
        ],

        'models' => [
            App\Models\Product::class,
        ]
    ],
```

It's possible to change the route path in the config. If you want to protect it with Auth for example, you may also attach custom middleware to the route in config.

### Customizing pagination links
Pagination links are part of the Laravel pagination. Check out the [Laravel docs](https://laravel.com/docs/5.7/pagination#customizing-the-pagination-view) on how to customize them.

<hr> 

## Examples

### Sorting

For sorting a model on multiple fields you would have a sort filter with values something like this: `sort=field-asc` and `sort=field-desc`:

```sh
php artisan distillery:filter Sort Product
```

```php
class Sort implements Filter
{
    protected static $allowed = ['price', 'name', 'updated_at'];
    
    public static function apply(Builder $builder, $value)
    {
        if (Str::endsWith($value, ['-asc', '-desc'])) {
            [$field, $dir] = explode('-', $value);
            
            if (in_array($field, static::$allowed)) {
                return $builder->orderBy($field, $dir);
            }
        }

        return $builder->orderBy('updated_at', 'desc');
    }
}
```
And apply to apply the filter just add it to the qs: `/product-list?search=socks&sort=price-desc`.

### Filtering relations

Sometimes you'll want to filter on relations of the model.

Suppose you have a Product model with multiple colors attached:

```php
class Color implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        $value = is_array($value) ? $value : [$value];
        $query = $builder->with('colors');

        foreach ($value as $colorId) {
            $query->whereHas('colors', function ($q) use ($colorId) {
                $q->where('id', $colorId);
            });
        }

        return $query;
    }
}
```
And to apply it: `/product-list?search=socks&sort=price-desc&color[]=2&color[]=5`.

## Roadmap to 1.0.0

- [ ] Add possibility to generate standard predefined filters (sort, search, ...).
- [x] Make possible to define which paramateres to hide from url query strings.
- [x] Add fallback to general filters that can be re-used across different models.
- [ ] Write tests.

## License

Laravel Distillery is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
