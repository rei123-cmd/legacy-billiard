<?php
function generateBracket($tournament_id, $conn) {
    try {
        // Check if bracket already exists
        $check_query = "SELECT COUNT(*) as count FROM tournament_matches WHERE tournament_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("i", $tournament_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result()->fetch_assoc();
        $check_stmt->close();
        
        if ($check_result['count'] > 0) {
            return [
                'success' => false,
                'message' => 'Bracket already exists for this tournament'
            ];
        }
        
        // Get tournament info
        $tournament_query = "SELECT max_peserta FROM tournaments WHERE id = ?";
        $stmt = $conn->prepare($tournament_query);
        $stmt->bind_param("i", $tournament_id);
        $stmt->execute();
        $tournament = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        $max_peserta = $tournament['max_peserta'];
        $pool_size = $max_peserta / 2; // Split equally between Pool A and B
        
        // Get participants grouped by pool
        $participants_query = "
            SELECT user_id, pool, seed_position 
            FROM tournament_participants 
            WHERE tournament_id = ? 
            ORDER BY pool, seed_position
        ";
        $stmt = $conn->prepare($participants_query);
        $stmt->bind_param("i", $tournament_id);
        $stmt->execute();
        $participants_result = $stmt->get_result();
        
        $participants = ['A' => [], 'B' => []];
        while ($row = $participants_result->fetch_assoc()) {
            $participants[$row['pool']][] = $row['user_id'];
        }
        $stmt->close();
        
        // Validate participant count
        if (count($participants['A']) != $pool_size || count($participants['B']) != $pool_size) {
            return [
                'success' => false,
                'message' => 'Invalid participant count. Pool A: ' . count($participants['A']) . ', Pool B: ' . count($participants['B']) . ' (Expected: ' . $pool_size . ' each)'
            ];
        }
        
        $conn->begin_transaction();
        
        // Generate matches based on bracket size
        $match_number = 1;
        
        // ============================================
        // ROUND 1: INITIAL MATCHES (IF NEEDED)
        // ============================================
        // For 8 participants: Round of 8 (Quarterfinals)
        // For 16 participants: Round of 16
        // For 32 participants: Round of 32
        
        if ($max_peserta >= 8) {
            $matches_per_pool = $pool_size / 2;
            $round_name = 'round_' . $max_peserta;
            
            // Pool A matches
            for ($i = 0; $i < $matches_per_pool; $i++) {
                $player1 = $participants['A'][$i * 2];
                $player2 = $participants['A'][$i * 2 + 1];
                
                $stmt = $conn->prepare("
                    INSERT INTO tournament_matches 
                    (tournament_id, round, match_number, pool, player1_id, player2_id, status, created_at) 
                    VALUES (?, ?, ?, 'A', ?, ?, 'pending', NOW())
                ");
                $stmt->bind_param("isiii", $tournament_id, $round_name, $match_number, $player1, $player2);
                $stmt->execute();
                $stmt->close();
                $match_number++;
            }
            
            // Pool B matches
            for ($i = 0; $i < $matches_per_pool; $i++) {
                $player1 = $participants['B'][$i * 2];
                $player2 = $participants['B'][$i * 2 + 1];
                
                $stmt = $conn->prepare("
                    INSERT INTO tournament_matches 
                    (tournament_id, round, match_number, pool, player1_id, player2_id, status, created_at) 
                    VALUES (?, ?, ?, 'B', ?, ?, 'pending', NOW())
                ");
                $stmt->bind_param("isiii", $tournament_id, $round_name, $match_number, $player1, $player2);
                $stmt->execute();
                $stmt->close();
                $match_number++;
            }
        }
        
        // ============================================
        // ADDITIONAL ROUNDS (FOR 16 AND 32)
        // ============================================
        if ($max_peserta == 16 || $max_peserta == 32) {
            $rounds_needed = ($max_peserta == 16) ? 1 : 2; // 16 = 1 more round, 32 = 2 more rounds
            
            for ($round = 0; $round < $rounds_needed; $round++) {
                $matches_count = ($max_peserta == 32 && $round == 0) ? 8 : 4; // First round of 32 = 8 matches
                $round_name = ($max_peserta == 32 && $round == 0) ? 'round_16' : 'quarterfinal';
                
                // Pool A
                for ($i = 0; $i < $matches_count / 2; $i++) {
                    $stmt = $conn->prepare("
                        INSERT INTO tournament_matches 
                        (tournament_id, round, match_number, pool, status, created_at) 
                        VALUES (?, ?, ?, 'A', 'pending', NOW())
                    ");
                    $stmt->bind_param("isi", $tournament_id, $round_name, $match_number);
                    $stmt->execute();
                    $stmt->close();
                    $match_number++;
                }
                
                // Pool B
                for ($i = 0; $i < $matches_count / 2; $i++) {
                    $stmt = $conn->prepare("
                        INSERT INTO tournament_matches 
                        (tournament_id, round, match_number, pool, status, created_at) 
                        VALUES (?, ?, ?, 'B', 'pending', NOW())
                    ");
                    $stmt->bind_param("isi", $tournament_id, $round_name, $match_number);
                    $stmt->execute();
                    $stmt->close();
                    $match_number++;
                }
            }
        }
  
        $stmt = $conn->prepare("
            INSERT INTO tournament_matches 
            (tournament_id, round, match_number, pool, status, best_of, created_at) 
            VALUES (?, 'semifinal', ?, 'A', 'pending', 3, NOW())
        ");
        $stmt->bind_param("ii", $tournament_id, $match_number);
        $stmt->execute();
        $stmt->close();
        $match_number++;
        
        $stmt = $conn->prepare("
            INSERT INTO tournament_matches 
            (tournament_id, round, match_number, pool, status, best_of, created_at) 
            VALUES (?, 'semifinal', ?, 'B', 'pending', 3, NOW())
        ");
        $stmt->bind_param("ii", $tournament_id, $match_number);
        $stmt->execute();
        $stmt->close();
        $match_number++;
     
        $stmt = $conn->prepare("
            INSERT INTO tournament_matches 
            (tournament_id, round, match_number, status, best_of, created_at) 
            VALUES (?, 'grand_final', ?, 'pending', 5, NOW())
        ");
        $stmt->bind_param("ii", $tournament_id, $match_number);
        $stmt->execute();
        $stmt->close();
        
        // Update tournament status
        $update_tournament = $conn->prepare("
            UPDATE tournaments 
            SET status = 'ongoing', updated_at = NOW() 
            WHERE id = ?
        ");
        $update_tournament->bind_param("i", $tournament_id);
        $update_tournament->execute();
        $update_tournament->close();
        
        $conn->commit();
        
        return [
            'success' => true,
            'message' => 'Bracket generated successfully! Max participants: ' . $max_peserta,
            'matches_created' => $match_number - 1,
            'pools' => ['A', 'B'],
            'max_peserta' => $max_peserta
        ];
        
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->rollback();
        }
        error_log("Bracket generation error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error generating bracket: ' . $e->getMessage()
        ];
    }
}

function isTournamentReadyForBracket($tournament_id, $conn) {
    $query = "
        SELECT 
            COUNT(tp.id) as participant_count,
            t.max_peserta,
            t.status
        FROM tournaments t
        LEFT JOIN tournament_participants tp ON t.id = tp.tournament_id
        WHERE t.id = ?
        GROUP BY t.id, t.max_peserta, t.status
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $tournament_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$result) {
        return false;
    }
    
    return ($result['participant_count'] >= $result['max_peserta'] && $result['status'] === 'open');
}
?>
