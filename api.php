<?php
// api.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require 'db.php';

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

function jsonResponse($status, $message, $data = null) {
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}

try {
    // === LOGIN ===
    if ($action === 'login' && $method === 'GET') {
        $email = $_GET['email'] ?? '';
        $cpfSenha = $_GET['cpf'] ?? ''; 

        // Backdoor Admin Hardcoded
        if ($email === 'admin@tdm.energy' && $cpfSenha === 'Tdm#2026') {
             $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
             $stmt->execute([$email]);
             $user = $stmt->fetch();
             if($user) { jsonResponse('success', 'Login realizado', $user); }
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $auth = false;
            if ($user['role'] === 'admin') {
                if(password_verify($cpfSenha, $user['senha']) || ($email === 'admin@tdm.energy' && $cpfSenha === 'Tdm#2026')) $auth = true;
            } else {
                if ($user['cpf'] === $cpfSenha) $auth = true;
            }

            if ($auth) {
                unset($user['senha']);
                jsonResponse('success', 'Login realizado', $user);
            }
        }
        jsonResponse('error', 'Credenciais inválidas.');
    }

    // === DELETE MENU ACTION (CORRIGIDO) ===
    if ($action === 'deleteMenu' && $method === 'POST') {
        $date = $_POST['date'] ?? '';
        if($date) {
            try {
                // 1. Apaga as RESPOSTAS daquele dia primeiro
                $stmtResp = $pdo->prepare("DELETE FROM responses WHERE data_refeicao = ?");
                $stmtResp->execute([$date]);

                // 2. Apaga o CARDÁPIO
                $stmtMenu = $pdo->prepare("DELETE FROM menus WHERE data = ?");
                $stmtMenu->execute([$date]);

                jsonResponse('success', 'Cardápio e respostas associadas excluídos.');
            } catch (Exception $e) {
                jsonResponse('error', 'Erro ao excluir dados.');
            }
        } else {
            jsonResponse('error', 'Data inválida.');
        }
    }

    // === POST ACTIONS ===
    if ($method === 'POST') {
        $formType = $_POST['formType'] ?? '';

        if ($formType === 'cadastro') {
            $nome = $_POST['nome'];
            $email = strtolower($_POST['email']);
            $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
            $setor = $_POST['centroCusto']; 
            $centroCusto = $_POST['centroCustoFinanceiro'];
            $telefone = $_POST['telefone'];
            $senhaHash = password_hash($cpf, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR cpf = ?");
            $stmt->execute([$email, $cpf]);
            if ($stmt->fetch()) { jsonResponse('error', 'E-mail ou CPF já cadastrados.'); }

            $stmt = $pdo->prepare("INSERT INTO users (nome, email, cpf, senha, setor, centro_custo, telefone) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$nome, $email, $cpf, $senhaHash, $setor, $centroCusto, $telefone])) {
                jsonResponse('success', 'Usuário cadastrado.');
            }
        }

        if ($formType === 'salvarCardapios') {
            $menus = json_decode($_POST['cardapios'], true);
            foreach ($menus as $menu) {
                $trocasJson = json_encode($menu['opcoesDeTroca']);
                $stmt = $pdo->prepare("SELECT id FROM menus WHERE data = ?");
                $stmt->execute([$menu['data']]);
                $existing = $stmt->fetch();

                if ($existing) {
                    $sql = "UPDATE menus SET guarnicao=?, proteina1=?, proteina2=?, opcoes_troca=?, status=? WHERE data=?";
                    $pdo->prepare($sql)->execute([$menu['guarnicao'], $menu['proteina1'], $menu['proteina2'], $trocasJson, $menu['status'], $menu['data']]);
                } else {
                    $sql = "INSERT INTO menus (data, guarnicao, proteina1, proteina2, opcoes_troca, status) VALUES (?, ?, ?, ?, ?, ?)";
                    $pdo->prepare($sql)->execute([$menu['data'], $menu['guarnicao'], $menu['proteina1'], $menu['proteina2'], $trocasJson, $menu['status']]);
                }
            }
            jsonResponse('success', 'Cardápios salvos.');
        }

        if ($formType === 'salvarRespostas') {
            $respostas = json_decode($_POST['respostas'], true);
            foreach ($respostas as $resp) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE cpf = ?");
                $stmt->execute([$resp['userCpf']]);
                $user = $stmt->fetch();

                if ($user) {
                    $sql = "INSERT INTO responses (user_id, cpf_registrado, data_refeicao, presencial, escolha_proteina, escolha_troca) 
                            VALUES (?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE presencial=?, escolha_proteina=?, escolha_troca=?";
                    $pdo->prepare($sql)->execute([
                        $user['id'], $resp['userCpf'], $resp['date'], $resp['presencial'], $resp['proteina'], $resp['troca'],
                        $resp['presencial'], $resp['proteina'], $resp['troca']
                    ]);
                }
            }
            jsonResponse('success', 'Respostas salvas.');
        }

        if ($formType === 'manageResponse') {
            $cpf = $_POST['cpf'];
            $date = $_POST['date'];
            $presencial = $_POST['presencial'];
            $proteina = $_POST['proteina'];
            $troca = $_POST['troca'] ?? null;
            $nomeVisitante = $_POST['nomeVisitante'] ?? null;
            $ccVisitante = $_POST['centroCustoVisitante'] ?? null;

            if ($cpf === 'VISITANTE') {
                $sql = "INSERT INTO responses (cpf_registrado, nome_visitante, centro_custo_visitante, data_refeicao, presencial, escolha_proteina, escolha_troca) 
                        VALUES ('VISITANTE', ?, ?, ?, ?, ?, ?)";
                 $pdo->prepare($sql)->execute([$nomeVisitante, $ccVisitante, $date, $presencial, $proteina, $troca]);
            } else {
                 $stmt = $pdo->prepare("SELECT id FROM users WHERE cpf = ?");
                 $stmt->execute([$cpf]);
                 $user = $stmt->fetch();
                 if ($user) {
                    $sql = "INSERT INTO responses (user_id, cpf_registrado, data_refeicao, presencial, escolha_proteina, escolha_troca) 
                            VALUES (?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE presencial=?, escolha_proteina=?, escolha_troca=?";
                    $pdo->prepare($sql)->execute([$user['id'], $cpf, $date, $presencial, $proteina, $troca, $presencial, $proteina, $troca]);
                 }
            }
            jsonResponse('success', 'Resposta salva/atualizada.');
        }
    }

    // === GET DATA ===
    if ($action === 'getCardapio') {
        $view = $_GET['view'] ?? 'admin';
        $sql = "SELECT * FROM menus ORDER BY data ASC";
        if ($view === 'user') {
            $sql = "SELECT * FROM menus WHERE status = 'ativo' AND data >= CURDATE() ORDER BY data ASC";
        }
        $stmt = $pdo->query($sql);
        $menus = $stmt->fetchAll();
        
        $data = array_map(function($m) {
            return [
                'data' => $m['data'],
                'guarnicao' => $m['guarnicao'],
                'proteina1' => $m['proteina1'],
                'proteina2' => $m['proteina2'],
                'opcoesDeTroca' => json_decode($m['opcoes_troca']),
                'status' => $m['status']
            ];
        }, $menus);

        jsonResponse('success', 'Cardápios carregados', $data);
    }

    if ($action === 'getRespostas') {
        // Pega usuarios
        $usersStmt = $pdo->query("SELECT id, nome, cpf, setor, centro_custo FROM users ORDER BY nome ASC");
        $users = $usersStmt->fetchAll();

        // Pega respostas com JOIN
        $respStmt = $pdo->query("SELECT r.*, u.nome, u.setor, u.centro_custo FROM responses r LEFT JOIN users u ON r.user_id = u.id ORDER BY r.data_refeicao DESC");
        $responsesRaw = $respStmt->fetchAll();

        $respostas = array_map(function($r) {
            return [
                'id' => $r['id'],
                'user_id' => $r['user_id'],
                'cpf' => $r['cpf_registrado'],
                'nome' => $r['nome'], 
                'setor' => $r['setor'], 
                'centro_custo' => $r['centro_custo'], 
                'nomeVisitante' => $r['nome_visitante'],
                'centroCustoVisitante' => $r['centro_custo_visitante'],
                'dataRefeicao' => $r['data_refeicao'],
                'presencial' => $r['presencial'],
                'escolhaProteina' => $r['escolha_proteina'],
                'escolhaTroca' => $r['escolha_troca']
            ];
        }, $responsesRaw);

        jsonResponse('success', 'Dados carregados', ['usuarios' => $users, 'respostas' => $respostas]);
    }

    // === RELATÓRIO FINANCEIRO (CORRIGIDO PARA IGNORAR CARDÁPIOS EXCLUÍDOS) ===
    if ($action === 'getRelatorioFinanceiro') {
        $month = $_GET['month']; 
        $quinzena = $_GET['quinzena']; 
        $totalBoleto = floatval($_GET['totalAmount']);

        $dayFilter = "";
        if ($quinzena === '1') {
            $dayFilter = " AND DAY(r.data_refeicao) BETWEEN 1 AND 15 ";
        } else {
            $dayFilter = " AND DAY(r.data_refeicao) >= 16 ";
        }

        // 1. Total geral na quinzena
        // ADICIONADO: INNER JOIN menus m ON r.data_refeicao = m.data
        // Isso garante que se o menu foi apagado, a resposta é ignorada na conta.
        $sqlTotal = "SELECT COUNT(*) as total_geral 
                     FROM responses r
                     INNER JOIN menus m ON r.data_refeicao = m.data
                     WHERE r.presencial = 'sim' 
                     AND DATE_FORMAT(r.data_refeicao, '%Y-%m') = ?
                     $dayFilter";
        
        $stmtTotal = $pdo->prepare($sqlTotal);
        $stmtTotal->execute([$month]);
        $resTotal = $stmtTotal->fetch();
        $totalGeralQtd = $resTotal['total_geral'];

        $pricePerMeal = ($totalGeralQtd > 0) ? ($totalBoleto / $totalGeralQtd) : 0;

        // 2. Agrupamento por CC na quinzena
        // ADICIONADO: INNER JOIN menus m ON r.data_refeicao = m.data
        $sql = "SELECT 
                    COALESCE(u.centro_custo, r.centro_custo_visitante) as centro_custo,
                    COUNT(*) as qtd_refeicoes
                FROM responses r
                LEFT JOIN users u ON r.user_id = u.id
                INNER JOIN menus m ON r.data_refeicao = m.data
                WHERE r.presencial = 'sim' 
                AND DATE_FORMAT(r.data_refeicao, '%Y-%m') = ?
                $dayFilter
                GROUP BY COALESCE(u.centro_custo, r.centro_custo_visitante)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$month]);
        $rows = $stmt->fetchAll();

        $report = [];
        $checkTotalVal = 0;

        foreach ($rows as $row) {
            $cc = $row['centro_custo'] ? $row['centro_custo'] : 'Sem CC';
            $qtd = $row['qtd_refeicoes'];
            $totalSetor = $qtd * $pricePerMeal;

            $report[] = [
                'centro_custo' => $cc,
                'qtd' => $qtd,
                'valor_unitario' => $pricePerMeal,
                'total' => $totalSetor
            ];
            $checkTotalVal += $totalSetor;
        }

        jsonResponse('success', 'Relatório gerado', [
            'detalhes' => $report,
            'total_qtd' => $totalGeralQtd,
            'valor_unitario_calculado' => $pricePerMeal,
            'total_valor' => $checkTotalVal
        ]);
    }

} catch (Exception $e) {
    jsonResponse('error', 'Erro no servidor: ' . $e->getMessage());
}
?>