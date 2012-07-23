<?php
declare(ticks=1);
$parentPid = posix_getpid();
$child1 = pcntl_fork();
if ($child1 != 0) {
    $child2 = pcntl_fork();
    if ($child2 != 0) {
		pcntl_setpriority(10);
		echo "Parent\n";
		$firstChildIsFree = true;
		$secondChildIsFree = true;
		pcntl_sigprocmask(SIG_BLOCK, array(SIGUSR1, SIGUSR2));
		while (true) {
			$changes = file_get_contents('db.txt');
			if (!empty($changes)) {
				$got2execution = false;
				if ($firstChildIsFree) {
					posix_kill($child1, SIGHUP); 
					$firstChildIsFree = false;
					$got2execution = true;
				} elseif ($secondChildIsFree) {
					posix_kill($child2, SIGHUP);
					$secondChildIsFree = false;
					$got2execution = true;
				}
				if ($got2execution) {
					file_put_contents('db.txt', '');
				} else {
					echo "No free workers\n";
				}
			}
			//Check signals from child processes
			if (pcntl_sigtimedwait(array(SIGUSR1, SIGUSR2), $siginfo, 0, 1) != -1) {
				if ($siginfo['signo'] == SIGUSR1) {
					echo "Got signal from child1\n";
					$firstChildIsFree = true;
				} elseif ($siginfo['signo'] == SIGUSR2) {
					echo "Got signal from child2\n";
					$secondChildIsFree = true;
				}
			}
			
			sleep(1);
		}
    } else {
		pcntl_setpriority(5);
		echo "Child2 waiting for SIGHUP\n";
		while (true) {
			pcntl_sigwaitinfo(array(SIGHUP));
			echo "Child2 has been activated\n";
			sleep(10);
			echo "Child2 is now free, notify parent #{$parentPid}\n";
			posix_kill($parentPid, SIGUSR2); 
		}
    }
} else {
	pcntl_setpriority(5);
    echo "Child1 waiting for SIGHUP\n";
    while (true) {
		pcntl_sigwaitinfo(array(SIGHUP));
		echo "Child1 has been activated\n";
		sleep(10);
		echo "Child1 is now free, notify parent #{$parentPid}\n";
		posix_kill($parentPid, SIGUSR1); 
	}
}
