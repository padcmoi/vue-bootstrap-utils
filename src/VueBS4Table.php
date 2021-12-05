<?php
namespace Padcmoi\VueBootstrap;

use Padcmoi\BundleApiSlim\Database;
use Padcmoi\BundleApiSlim\Misc;
use Padcmoi\BundleApiSlim\SanitizeData;
use PDO;

class VueBS4Table
{

    /**
     * Retourne les items avec une requete SQL conçu pour Bootstrap Table
     * @param {string} req SQL
     * @param {array} req préparée
     *
     * @return {array}
     */
    public static function items(string $req, array $bindValue = [])
    {
        $sth = Database::request()->prepare($req);

        foreach ($bindValue as $obj) {
            foreach ($obj as $v) {
                if (!isset($obj['key']) || !isset($obj['value']) || !isset($obj['param'])) {
                    continue;
                }

                $sth->bindValue($obj['key'], $obj['value'], $obj['param']);
            }
        }

        $sth->execute();
        $sth->setFetchMode(PDO::FETCH_NAMED); // Uniquement des clés nommées

        $items = $sth->fetchAll();
        $sth->closeCursor();

        return $items;
    }

    /**
     * Construit une requete orderBy
     * @param {array} $allowedSortBy
     *
     * @return {string}
     */
    public static function orderBy(array $allowedSortBy = [])
    {
        $input = SanitizeData::clean();

        $sortBy = isset($input['sortBy']) ? $input['sortBy'] : '';
        $sortDesc = isset($input['sortDesc']) && Misc::convertStrToBool($input['sortDesc']) ? 'DESC' : 'ASC';

        // Concernant la sécurité & l'escape SQL !!! les variables $sortBy & $sortDesc sont sécurisées
        // si $sortBy est pas dans la liste de $allowedSortBy alors la requete ne sera pas construite
        // peu importe ce que recevra sortDesc, la valeur finale sera soit DESC soit ASC
        if (!in_array($sortBy, $allowedSortBy)) {
            return ' ';
        }

        return " ORDER BY `" . $sortBy . "` " . $sortDesc . " ";
    }

    /**
     * Retourne uniquement les valeurs pour la clause LIMIT
     * calcul le currentPage
     *
     * @return {array}
     */
    public static function limit($args = null, int $limitPerPage = 50)
    {
        if (gettype($args) === "array") {
            $perPage = isset($args['per_page']) ? intval($args['per_page']) : 5;
            $currentPage = isset($args['current_page']) ? intval($args['current_page'] - 1) : 0;
        } else {
            $input = SanitizeData::clean();
            $perPage = isset($input['perPage']) ? intval($input['perPage']) : 5;
            $currentPage = isset($input['currentPage']) ? intval($input['currentPage'] - 1) : 0;
        }

        if ($perPage >= $limitPerPage) {$perPage = $limitPerPage;}

        $currentPage = $currentPage * $perPage;

        return ['limit' => intval($currentPage), 'offset' => intval($perPage)];
    }

    /**
     * Pour une recherche, pour la clause WHERE
     *
     * @return {string}
     */
    public static function filter()
    {
        $input = SanitizeData::clean();
        return isset($input['filter']) ? '%' . $input['filter'] . '%' : '%%';
    }

    /**
     * Construit la requete search pour la clause WHERE
     * @param {array} les colonnes de recherche
     * @param {string}
     *
     * @return {string}
     */
    public static function clauseForFilter(array $searchs = [], string $keyId)
    {
        $str = array_map(function ($v) {
            $v = "LCASE(`" . strtolower($v) . "`)";
            return $v;
        }, $searchs);

        $str = implode(' LIKE :' . $keyId . ' OR ', $str);

        if (count($searchs) >= 1) {$str .= " LIKE :" . $keyId;}

        return " (" . $str . ") ";
    }
}