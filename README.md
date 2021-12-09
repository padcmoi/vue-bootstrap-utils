# Component PHP for VueBootstrap


# ➡️Install
```
composer require padcmoi/vue-bootstrap-utils-for-php-api
```


# ➡️Usage
Example Usage in a project
```PHP
use Padcmoi\VueBootstrap\VueBS4Table;

class Table
{
    protected static function items($args = null)
    {
        $SELECTOR = ["id", "created_at", "username"];
        $SEARCHS_FILTER = ["id", "created_at", "username"];

        $extended_selector = ""; // pour ajouter des fonctions SQL

        return VueBS4Table::easyItems('users', [
            'args' => $args,
            'selector' => $SELECTOR,
            'orderBy' => $SELECTOR,
            'searchFilter' => $SEARCHS_FILTER,
            'add_before_where' => [
                'key' => 'id',
                'comparaison' => '>=',
                'bind' => ':bind',
                'value' => '197',
            ]
        ]);
    }
}
```

# ➡️Others
##### 🧳Packagist
https://packagist.org/packages/padcmoi/vue-bootstrap-utils-for-php-api

##### 🔖Licence
Ce travail est sous licence [MIT](/LICENSE).

##### 🔥Pour me contacter sur discord
Lien discord [discord.gg/257rUb9](https://discord.gg/257rUb9)

##### 🍺Si vous souhaitez m’offrir un pourboire
Me faire un don 😍 [par Paypal](https://www.paypal.com/paypalme/Julien06100?locale.x=fr_FR)
