# Ok Bloomer
Autoscaling Bloom filter with ultra low memory usage for PHP. Ok Bloomer, employs a layering strategy that allows it to grow as more items are added to the filter while maintaining an upper bound on the false positive rate.

- Ultra low memory footprint
- Works on streaming data
- Open-source and free to use commercially

> **Note:** Due to the probabilistic nature of the Bloom filter, it may report false positives at a bounded rate.

## Installation
Install into your project using [Composer](https://getcomposer.org/):

```sh
$ composer require scienide/okbloomer
```

### Requirements
- [PHP](https://php.net/manual/en/install.php) 7.4 or above

## Example Usage

```php
use OkBloomer\BloomFilter;

$filter = new BloomFilter(0.001, 4);

echo $filter->existsOrInsert('foo');

echo $filter->exists('foo');
```

```
false 

true
```

## Testing
To run the unit tests:

```sh
$ composer test
```
## Static Analysis
To run static code analysis:

```sh
$ composer analyze
```

## Benchmarks
To run the benchmarks:

```sh
$ composer benchmark
```

## References
- [1] P. S. Almeida et al. (2007). Scalable Bloom Filters.
