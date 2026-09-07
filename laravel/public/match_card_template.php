<?php
/**
 * ============================================
 * MATCH CARD TEMPLATE - REUSABLE COMPONENT
 * ============================================
 * This template displays individual tournament matches
 * Can be included in bracket displays
 * Variables expected: $match (array with match data)
 * ============================================
 */

if (!isset($match)) {
    echo '<div class="alert alert-danger">Error: Match data not provided</div>';
    return;
}

$is_completed = ($match['status'] === 'completed');
$has_players = ($match['player1_id'] && $match['player2_id']);
$is_waiting = !$has_players;
?>

<div class="match-card <?= $is_completed ? 'completed' : '' ?> <?= !$is_completed && $has_players ? 'in-progress' : '' ?> fade-in">
    <!-- Match Label -->
    <div class="match-label">
        <i class="fas fa-chess-board"></i>
        MATCH #<?= $match['match_number'] ?>
    </div>
    
    <?php if ($match['created_at']): ?>
        <div class="match-time">
            <i class="far fa-clock"></i>
            <?= date('d M Y, H:i', strtotime($match['created_at'])) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($is_waiting): ?>
        <!-- WAITING FOR PLAYERS -->
        <div class="match-waiting">
            <i class="fas fa-hourglass-half"></i>
            Waiting for players from previous round...
        </div>
        
    <?php elseif (!$is_completed): ?>
        <!-- MATCH IN PROGRESS - SET WINNER FORM -->
        <form method="POST" action="admin_tournament_bracket.php?id=<?= $tournament_id ?>" onsubmit="return confirm('Are you sure you want to set this winner? This cannot be easily undone!');">
            <input type="hidden" name="action" value="set_winner">
            <input type="hidden" name="match_id" value="<?= $match['id'] ?>">
            
            <!-- PLAYER 1 -->
            <div class="player-row">
                <div class="player-info">
                    <img src="images/<?= $match['player1_photo'] ?? 'avataruser.jpg' ?>" 
                         alt="Player 1" 
                         class="player-avatar"
                         onerror="this.src='images/avataruser.jpg'">
                    <div class="player-details">
                        <div class="player-name"><?= htmlspecialchars($match['player1_name'] ?? 'TBD') ?></div>
                        <div class="player-email"><?= htmlspecialchars($match['player1_email'] ?? '') ?></div>
                    </div>
                </div>
                <input type="number" 
                       name="player1_score" 
                       class="score-input" 
                       min="0" 
                       max="99" 
                       placeholder="0" 
                       required>
            </div>
            
            <!-- VS TEXT -->
            <div class="vs-text">VS</div>
            
            <!-- PLAYER 2 -->
            <div class="player-row">
                <div class="player-info">
                    <img src="images/<?= $match['player2_photo'] ?? 'avataruser.jpg' ?>" 
                         alt="Player 2" 
                         class="player-avatar"
                         onerror="this.src='images/avataruser.jpg'">
                    <div class="player-details">
                        <div class="player-name"><?= htmlspecialchars($match['player2_name'] ?? 'TBD') ?></div>
                        <div class="player-email"><?= htmlspecialchars($match['player2_email'] ?? '') ?></div>
                    </div>
                </div>
                <input type="number" 
                       name="player2_score" 
                       class="score-input" 
                       min="0" 
                       max="99" 
                       placeholder="0" 
                       required>
            </div>
            
            <!-- WINNER SELECTION -->
            <div class="winner-controls">
                <label>
                    <i class="fas fa-crown"></i>
                    Select Winner:
                </label>
                <div class="winner-buttons">
                    <button type="submit" 
                            name="winner_id" 
                            value="<?= $match['player1_id'] ?>" 
                            class="btn-winner">
                        <i class="fas fa-trophy"></i>
                        <?= htmlspecialchars($match['player1_name'] ?? 'Player 1') ?>
                    </button>
                    <button type="submit" 
                            name="winner_id" 
                            value="<?= $match['player2_id'] ?>" 
                            class="btn-winner">
                        <i class="fas fa-trophy"></i>
                        <?= htmlspecialchars($match['player2_name'] ?? 'Player 2') ?>
                    </button>
                </div>
            </div>
        </form>
        
    <?php else: ?>
        <!-- MATCH COMPLETED - SHOW RESULTS -->
        
        <!-- PLAYER 1 -->
        <div class="player-row <?= ($match['winner_id'] == $match['player1_id']) ? 'winner' : 'loser' ?>">
            <div class="player-info">
                <img src="images/<?= $match['player1_photo'] ?? 'avataruser.jpg' ?>" 
                     alt="Player 1" 
                     class="player-avatar"
                     onerror="this.src='images/avataruser.jpg'">
                <div class="player-details">
                    <div class="player-name"><?= htmlspecialchars($match['player1_name'] ?? 'TBD') ?></div>
                    <div class="player-email"><?= htmlspecialchars($match['player1_email'] ?? '') ?></div>
                </div>
            </div>
            <div class="score-display"><?= $match['player1_score'] ?? 0 ?></div>
        </div>
        
        <!-- VS TEXT -->
        <div class="vs-text">VS</div>
        
        <!-- PLAYER 2 -->
        <div class="player-row <?= ($match['winner_id'] == $match['player2_id']) ? 'winner' : 'loser' ?>">
            <div class="player-info">
                <img src="images/<?= $match['player2_photo'] ?? 'avataruser.jpg' ?>" 
                     alt="Player 2" 
                     class="player-avatar"
                     onerror="this.src='images/avataruser.jpg'">
                <div class="player-details">
                    <div class="player-name"><?= htmlspecialchars($match['player2_name'] ?? 'TBD') ?></div>
                    <div class="player-email"><?= htmlspecialchars($match['player2_email'] ?? '') ?></div>
                </div>
            </div>
            <div class="score-display"><?= $match['player2_score'] ?? 0 ?></div>
        </div>
        
        <!-- MATCH RESULT -->
        <div class="match-result">
            <i class="fas fa-check-circle"></i>
            <strong><?= htmlspecialchars($match['winner_name']) ?></strong> wins!
            <?php if ($match['completed_at']): ?>
                <br>
                <small>Completed: <?= date('d M Y, H:i', strtotime($match['completed_at'])) ?></small>
            <?php endif; ?>
        </div>
        
        <!-- MATCH ACTIONS -->
        <div class="match-actions">
            <a href="admin_tournament_bracket.php?id=<?= $tournament_id ?>&action=reset_match&match_id=<?= $match['id'] ?>" 
               class="btn-action btn-reset"
               onclick="return confirm('Are you sure you want to reset this match? Players will need to play again!');">
                <i class="fas fa-undo"></i>
                Reset Match
            </a>
        </div>
    <?php endif; ?>
</div>
