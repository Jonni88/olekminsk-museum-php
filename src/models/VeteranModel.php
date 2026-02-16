<?php
require_once __DIR__ . '/../config/database.php';

class VeteranModel {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    // Получить список ветеранов (с фильтрами)
    public function getAll($filters = [], $page = 1, $perPage = ITEMS_PER_PAGE) {
        $where = ['status = ?'];
        $params = ['approved'];
        
        if (!empty($filters['search'])) {
            $where[] = "(last_name LIKE ? OR first_name LIKE ? OR patronymic LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        if (!empty($filters['settlement'])) {
            $where[] = "settlement = ?";
            $params[] = $filters['settlement'];
        }
        
        $whereClause = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;
        
        // Всего записей
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM veterans WHERE $whereClause");
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        
        // Сами записи
        $sql = "SELECT * FROM veterans WHERE $whereClause 
                ORDER BY last_name, first_name 
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($params, [$perPage, $offset]));
        
        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current' => $page
        ];
    }
    
    // Получить одного ветерана
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM veterans WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Добавить ветерана (из формы)
    public function create($data) {
        $sql = "INSERT INTO veterans 
                (last_name, first_name, patronymic, birth_year, death_year, 
                 settlement, rank, awards, biography, front_path,
                 submitted_by, submitter_contact, status, photos)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['last_name'],
            $data['first_name'],
            $data['patronymic'] ?? null,
            $data['birth_year'] ?? null,
            $data['death_year'] ?? null,
            $data['settlement'] ?? null,
            $data['rank'] ?? null,
            $data['awards'] ?? null,
            $data['biography'] ?? null,
            $data['front_path'] ?? null,
            $data['submitted_by'] ?? 'Аноним',
            $data['submitter_contact'] ?? null,
            !empty($data['photos']) ? json_encode($data['photos']) : null
        ]);
        
        return $this->db->lastInsertId();
    }
    
    // Обновить ветерана
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, ['last_name', 'first_name', 'patronymic', 'birth_year', 
                'death_year', 'settlement', 'rank', 'awards', 'biography', 'front_path'])) {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }
        
        if (empty($fields)) return false;
        
        $params[] = $id;
        $sql = "UPDATE veterans SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    // Одобрить/отклонить
    public function setStatus($id, $status, $adminId = null) {
        $sql = "UPDATE veterans SET status = ?, approved_at = NOW(), approved_by = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $adminId, $id]);
    }
    
    // Получить на модерацию
    public function getPending() {
        $stmt = $this->db->prepare(
            "SELECT * FROM veterans WHERE status = 'pending' ORDER BY created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Получить все населённые пункты (для фильтра)
    public function getSettlements() {
        $stmt = $this->db->query(
            "SELECT DISTINCT settlement FROM veterans 
             WHERE settlement IS NOT NULL AND status = 'approved' 
             ORDER BY settlement"
        );
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    // Увеличить счётчик просмотров
    public function incrementViews($id) {
        $stmt = $this->db->prepare("UPDATE veterans SET views_count = views_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    // Получить для бессмертного полка
    public function getForPolk($limit = 100) {
        $stmt = $this->db->prepare(
            "SELECT id, last_name, first_name, patronymic, photo_main 
             FROM veterans 
             WHERE status = 'approved' AND (photo_main IS NOT NULL OR photo_main != '') 
             ORDER BY RAND() 
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    // Статистика
    public function getStats() {
        $stats = [];
        
        $stats['total'] = $this->db->query("SELECT COUNT(*) FROM veterans WHERE status = 'approved'")->fetchColumn();
        $stats['pending'] = $this->db->query("SELECT COUNT(*) FROM veterans WHERE status = 'pending'")->fetchColumn();
        $stats['with_photo'] = $this->db->query("SELECT COUNT(*) FROM veterans WHERE status = 'approved' AND photo_main IS NOT NULL")->fetchColumn();
        
        return $stats;
    }
}
