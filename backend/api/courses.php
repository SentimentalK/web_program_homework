<?php
require_once __DIR__ . '/../utils/db.php';
require_once __DIR__ . '/../utils/tools.php';




function purchaseAdd() {
    global $db;
    $userId = $_SERVER['AUTH_USER_ID'];
    $data = getJsonData();

    
    check_required_fields($data, ['course_ids']);
    
    if (!is_array($data['course_ids']) || empty($data['course_ids'])) {
        throw new Exception('Invalid course_ids format', 400);
    }

    try {
        $db->beginTransaction();
        
        $stmt = $db->prepare("
            INSERT INTO purchases (user_id, course_id) 
            VALUES (:user_id, :course_id)
            ON CONFLICT(user_id, course_id) DO NOTHING
        ");

        foreach ($data['course_ids'] as $courseId) {
            $stmt->execute([
                ':user_id' => $userId,
                ':course_id' => (int)$courseId
            ]);
        }

        $db->commit();

        echo json_encode([
            'code' => 200,
            'msg' => 'Purchases added',
            'count' => count($data['course_ids'])
        ]);
        
    } catch (PDOException $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode([
            'code' => 500,
            'msg' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

function purchaseRemove() {
    global $db;
    $userId = $_SERVER['AUTH_USER_ID'];
    $data = getJsonData();

    
    check_required_fields($data, ['course_ids']);
    
    if (!is_array($data['course_ids']) || empty($data['course_ids'])) {
        throw new Exception('Invalid course_ids format', 400);
    }

    try {
        $db->beginTransaction();

        
        $placeholders = implode(',', array_fill(0, count($data['course_ids']), '?'));
        $stmt = $db->prepare("
            DELETE FROM purchases 
            WHERE user_id = ? 
            AND course_id IN ($placeholders)
        ");
        
        $params = array_merge([$userId], $data['course_ids']);
        $stmt->execute($params);

        $db->commit();

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'code' => 200,
                'msg' => 'Purchases removed',
                'affected_rows' => $stmt->rowCount()
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'code' => 404,
                'msg' => 'No matching purchases found'
            ]);
        }
    } catch (PDOException $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode([
            'code' => 500,
            'msg' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

function purchaseValidate()
{
    global $db;
    global $db;
    if ($_SERVER['HTTP_AUTHORIZATION']){
        validateToken();
    }
    $userId = $_SERVER['AUTH_USER_ID'] ?? 0;
    $data = getJsonData();

    check_required_fields($data, ['course_id']);

    try {
        $stmt = $db->prepare("SELECT course_id FROM purchases WHERE user_id = :user_id and course_id = :course_id 
                                     UNION SELECT course_id FROM courses WHERE course_id = :free_course_id AND free = 1");
        $stmt->execute([
            ':user_id' => $userId,
            ':course_id' => (int) $data['course_id'],
            ':free_course_id' => (int) $data['course_id']
        ]);

        echo json_encode([
            'code' => 200,
            'valid' => (bool) $stmt->fetch(PDO::FETCH_ASSOC),
            'debug' => 1
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['code' => 500, 'msg' => 'Database error: ' . $e->getMessage()]);
    }
}

function listAllCourses()
{
    global $db;
    if ($_SERVER['HTTP_AUTHORIZATION']){
        validateToken();
    }
    $userId = $_SERVER['AUTH_USER_ID'] ?? 0;

    try {
        if ($userId) {
            $query = "SELECT c.course_id, 
                    CASE WHEN p.user_id IS NOT NULL THEN 2 WHEN c.free = 1 THEN 1 ELSE 0 END AS status 
                  FROM courses c 
                  LEFT JOIN purchases p ON c.course_id = p.course_id AND p.user_id = :user_id;";
            $stmt = $db->prepare($query);
            $stmt->execute([':user_id' => $userId]);

        } else {
            $query = "SELECT course_id, free as `status` FROM courses ORDER BY course_id LIMIT 5";
            $stmt = $db->prepare($query);
            $stmt->execute();
        }

        echo json_encode([
            'code' => 200,
            'course_ids' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['code' => 500, 'msg' => 'Database error: ' . $e->getMessage()]);
    }
}