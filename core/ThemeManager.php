<?php
// 📁 core/ThemeManager.php
class ThemeManager
{
    private $db;
    private $currentTheme;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }

    public function getTheme($themeId = null)
    {
        if (!$themeId) {
            $themeId = $this->getDefaultThemeId();
        }

        $sql = 'SELECT * FROM themes WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$themeId]);
        $theme = $stmt->fetch();

        if ($theme) {
            $this->currentTheme = $theme;

            return $this->generateCSSVariables($theme);
        }

        return $this->getDefaultTheme();
    }

    private function generateCSSVariables($themeData)
    {
        $colors = json_decode($themeData['colors'], true);

        return [
            'primary' => $colors['primary'] ?? '#2c3e50',
            'secondary' => $colors['secondary'] ?? '#3498db',
            'success' => $colors['success'] ?? '#27ae60',
            'warning' => $colors['warning'] ?? '#f39c12',
            'danger' => $colors['danger'] ?? '#e74c3c',
            'light' => $colors['light'] ?? '#ecf0f1',
            'dark' => $colors['dark'] ?? '#34495e',
            'border_radius' => '8px',
            'box_shadow' => '0 4px 6px rgba(0,0,0,0.1)',
            'transition' => 'all 0.3s ease',
        ];
    }

    public function createCustomTheme($themeName, $colors, $userId)
    {
        $sql = 'INSERT INTO themes (name, colors, created_by) VALUES (?, ?, ?)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $themeName,
            json_encode($colors),
            $userId,
        ]);

        return $this->db->lastInsertId();
    }

    public function getUserThemes($userId)
    {
        $sql = 'SELECT * FROM themes WHERE created_by = ? OR is_active = 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    private function getDefaultThemeId()
    {
        $sql = 'SELECT id FROM themes WHERE is_active = 1 LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $theme = $stmt->fetch();

        return $theme ? $theme['id'] : 1;
    }

    public function renderThemeCSS()
    {
        $theme = $this->getTheme();
        ob_start();
        ?>
        <style>
            :root {
                --primary-color: <?php echo $theme['primary']; ?>;
                --secondary-color: <?php echo $theme['secondary']; ?>;
                --success-color: <?php echo $theme['success']; ?>;
                --warning-color: <?php echo $theme['warning']; ?>;
                --danger-color: <?php echo $theme['danger']; ?>;
                --light-color: <?php echo $theme['light']; ?>;
                --dark-color: <?php echo $theme['dark']; ?>;
                --border-radius: <?php echo $theme['border_radius']; ?>;
                --box-shadow: <?php echo $theme['box_shadow']; ?>;
                --transition: <?php echo $theme['transition']; ?>;
            }
            
            .bg-primary { background-color: var(--primary-color) !important; }
            .text-primary { color: var(--primary-color) !important; }
            .btn-primary { 
                background-color: var(--primary-color); 
                border-color: var(--primary-color);
            }
            .border-primary { border-color: var(--primary-color) !important; }
        </style>
        <?php
        return ob_get_clean();
    }
}
?>