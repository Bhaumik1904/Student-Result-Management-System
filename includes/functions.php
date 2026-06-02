<?php
/**
 * Core Helper Functions
 * Student Result Management System
 */

// =====================================================
// RESULT CALCULATION FUNCTIONS
// =====================================================

/**
 * Calculate total marks obtained by a student
 */
function calculateTotal(array $marks): float {
    return array_sum(array_column($marks, 'marks_obtained'));
}

/**
 * Calculate total maximum marks
 */
function calculateMaxTotal(array $marks): int {
    return array_sum(array_column($marks, 'max_marks'));
}

/**
 * Calculate percentage
 * Formula: (Total Obtained / Total Maximum) × 100
 */
function calculatePercentage(float $obtained, int $maxTotal): float {
    if ($maxTotal <= 0) return 0.0;
    return round(($obtained / $maxTotal) * 100, 2);
}

/**
 * Calculate grade based on percentage
 * 80+: A | 70-79: B | 60-69: C | 50-59: D | <50: F
 */
function calculateGrade(float $percentage): string {
    if ($percentage >= 80) return 'A';
    if ($percentage >= 70) return 'B';
    if ($percentage >= 60) return 'C';
    if ($percentage >= 50) return 'D';
    return 'F';
}

/**
 * Get grade label
 */
function getGradeLabel(string $grade): string {
    $labels = [
        'A' => 'Outstanding',
        'B' => 'Excellent',
        'C' => 'Good',
        'D' => 'Average',
        'F' => 'Fail',
    ];
    return $labels[$grade] ?? 'Unknown';
}

/**
 * Calculate result status (PASS/FAIL)
 * Pass mark per subject = 35
 * If ANY subject < 35 → FAIL
 */
function calculateResultStatus(array $marks): string {
    $passMarkPerSubject = 35;
    foreach ($marks as $mark) {
        if ((float)$mark['marks_obtained'] < $passMarkPerSubject) {
            return 'FAIL';
        }
    }
    return 'PASS';
}

/**
 * Get complete result data for a student
 */
function getStudentResult(PDO $pdo, int $studentId): array|null {
    // Get student info
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch();
    if (!$student) return null;

    // Get marks with subject info
    $stmt = $pdo->prepare("
        SELECT m.marks_obtained, s.subject_code, s.subject_name, s.max_marks
        FROM marks m
        JOIN subjects s ON m.subject_id = s.id
        WHERE m.student_id = ?
        ORDER BY s.subject_code
    ");
    $stmt->execute([$studentId]);
    $marks = $stmt->fetchAll();

    if (empty($marks)) return ['student' => $student, 'marks' => [], 'summary' => null];

    $totalObtained = calculateTotal($marks);
    $totalMax      = calculateMaxTotal($marks);
    $percentage    = calculatePercentage($totalObtained, $totalMax);
    $grade         = calculateGrade($percentage);
    $status        = calculateResultStatus($marks);

    return [
        'student'  => $student,
        'marks'    => $marks,
        'summary'  => [
            'total_obtained' => $totalObtained,
            'total_max'      => $totalMax,
            'percentage'     => $percentage,
            'grade'          => $grade,
            'grade_label'    => getGradeLabel($grade),
            'status'         => $status,
        ],
    ];
}

// =====================================================
// SECURITY / SANITIZATION
// =====================================================

/**
 * Sanitize string input
 */
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Output CSRF hidden field
 */
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . generateCsrfToken() . '">';
}

// =====================================================
// RESPONSE HELPERS
// =====================================================

/**
 * Send JSON response and exit
 */
function jsonResponse(bool $success, string $message, array $data = []): void {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

/**
 * Redirect helper
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

// =====================================================
// DASHBOARD STATS
// =====================================================

/**
 * Get dashboard statistics
 */
function getDashboardStats(PDO $pdo): array {
    $stats = [];

    $stats['total_students'] = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $stats['total_subjects'] = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();

    // Students who have marks entered
    $studentsWithMarks = $pdo->query("SELECT DISTINCT student_id FROM marks")->fetchAll(PDO::FETCH_COLUMN);
    $pass = 0; $fail = 0; $totalPct = 0; $count = 0;

    foreach ($studentsWithMarks as $sid) {
        $result = getStudentResult($pdo, (int)$sid);
        if ($result && $result['summary']) {
            $count++;
            $totalPct += $result['summary']['percentage'];
            if ($result['summary']['status'] === 'PASS') $pass++;
            else $fail++;
        }
    }

    $stats['pass_students']    = $pass;
    $stats['fail_students']    = $fail;
    $stats['avg_percentage']   = $count > 0 ? round($totalPct / $count, 2) : 0;

    return $stats;
}

/**
 * Get top N students by percentage
 */
function getToppers(PDO $pdo, int $limit = 5): array {
    $studentsWithMarks = $pdo->query("SELECT DISTINCT student_id FROM marks")->fetchAll(PDO::FETCH_COLUMN);
    $toppers = [];

    foreach ($studentsWithMarks as $sid) {
        $result = getStudentResult($pdo, (int)$sid);
        if ($result && $result['summary']) {
            $toppers[] = [
                'roll_number'   => $result['student']['roll_number'],
                'student_name'  => $result['student']['student_name'],
                'department'    => $result['student']['department'],
                'percentage'    => $result['summary']['percentage'],
                'grade'         => $result['summary']['grade'],
                'status'        => $result['summary']['status'],
            ];
        }
    }

    usort($toppers, fn($a, $b) => $b['percentage'] <=> $a['percentage']);
    return array_slice($toppers, 0, $limit);
}

/**
 * Get department distribution
 */
function getDepartmentDistribution(PDO $pdo): array {
    $stmt = $pdo->query("SELECT department, COUNT(*) as count FROM students GROUP BY department");
    return $stmt->fetchAll();
}

/**
 * Get recently added students
 */
function getRecentStudents(PDO $pdo, int $limit = 5): array {
    $stmt = $pdo->prepare("SELECT * FROM students ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}
