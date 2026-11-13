
<?php
include 'condb.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? '';

try {

    // ดึงข้อมูลประเภทสินค้า
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        $catStmt = $conn->prepare("
            SELECT * FROM categorys
            ORDER BY category_id ASC
        ");
        $catStmt->execute();

        $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "data" => $categories,
            "categories" => $categories
        ]);
        exit;
    }

    // เพิ่มประเภทสินค้า
    if ($action === 'add') {

        $name = $_POST['category_name'];

        $stmt = $conn->prepare("
            INSERT INTO categorys (category_name)
            VALUES (?)
        ");

        $stmt->execute([$name]);

        echo json_encode([
            "success" => true,
            "message" => "เพิ่มประเภทสินค้าเรียบร้อย"
        ]);
        exit;
    }

    // แก้ไขประเภทสินค้า
    if ($action === 'update') {

        $id = $_POST['category_id'];
        $name = $_POST['category_name'];

        $sql = "
            UPDATE categorys
            SET category_name = ?
            WHERE category_id = ?
        ";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$name, $id]);

        echo json_encode([
            "success" => true,
            "message" => "แก้ไขประเภทสินค้าสำเร็จ"
        ]);
        exit;
    }

    // ลบประเภทสินค้า
    if ($action === 'delete') {

        $id = $_POST['category_id'];

        $stmt = $conn->prepare("DELETE FROM categorys WHERE category_id = ?");

        $stmt->execute([$id]);

        echo json_encode([
            "success" => true,
            "message" => "ลบประเภทสินค้าเรียบร้อย"
        ]);
        exit;
    }

    // กรณีไม่พบ action
    echo json_encode([
        "success" => false,
        "message" => "ไม่พบคำสั่งที่ต้องการ"
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>
