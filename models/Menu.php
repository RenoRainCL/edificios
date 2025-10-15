<?php
class Menu {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }
    
    public function getUserMenu($userId, $roleId) {
        $sql = "SELECT mi.* FROM menu_items mi WHERE mi.is_active = 1 ORDER BY mi.menu_order ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $allMenuItems = $stmt->fetchAll();
        return $this->buildMenuTree($allMenuItems);
    }
    private function buildMenuTree($menuItems, $parentId = null) {
        $tree = [];

        foreach ($menuItems as $item) {
            // CAMBIO: Usar 'parent_id' en lugar de 'padre_id'
            if ($item['parent_id'] == $parentId) {
                // Recursión para buscar submenús
                $children = $this->buildMenuTree($menuItems, $item['id']);

                if (!empty($children)) {
                    $item['children'] = $children;
                }

                $tree[] = $item;
            }
        }

        return $tree;
    }
}
?>
