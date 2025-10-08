# Reset Course Completion in Moodle (Docker)

This guide explains how to manually reset and re-trigger course completion for a specific user in a Moodle Docker environment. It’s useful for plugin developers who need to repeatedly test event observers (e.g., Navigatr badge issuance).

---

## Prerequisites

- You’re running Moodle in a Docker environment (via moodle-docker).
- You have shell access to the Moodle webserver container.
- You know the **user ID** and **course ID** you want to reset.

---

## 1. Enter the webserver container

```bash
cd /path/to/your/moodle-docker

# Set up environment variables
export MOODLE_DOCKER_WWWROOT=$PWD/moodle
export MOODLE_DOCKER_WEB_PORT=9000
export COMPOSE_PROJECT_NAME=moodle5

bin/moodle-docker-compose exec webserver bash
```

You should now be inside the container (prompt looks like `root@…:/var/www/html#`).

---

## 2. Clear course completion for a user

This removes the course completion record for a specific user.

```bash
php -r '
define("CLI_SCRIPT", true);
require_once("/var/www/html/config.php");
require_once($CFG->libdir . "/completionlib.php");
$userid = 10; $courseid = 2;

if ($comp = completion_completion::fetch(["userid"=>$userid, "course"=>$courseid])) {
    $comp->delete();
    echo "✅ Cleared course completion for user {$userid} in course {$courseid}\\n";
} else {
    echo "ℹ️ No completion record existed for user {$userid} in course {$courseid}\\n";
}
'
```

---

## 3. Clear activity completion for the same user

Moodle may automatically re-mark the course as complete if all activity completion criteria remain satisfied.

```bash
php -r '
define("CLI_SCRIPT", true);
require_once("/var/www/html/config.php");
require_once($CFG->libdir . "/completionlib.php");

$userid = 10; $courseid = 2;
$course = get_course($courseid);
$cinfo = new completion_info($course);
$cms = $cinfo->get_activities();

$cleared = 0;
foreach ($cms as $cm) {
    if ($cinfo->is_enabled($cm) != COMPLETION_TRACKING_NONE) {
        $cinfo->update_state($cm, COMPLETION_INCOMPLETE, $userid);
        $cleared++;
    }
}
echo "✅ Cleared completion on $cleared activities for user {$userid} in course {$courseid}\\n";
'
```

---

## 4. Set the course to "In Progress"

```bash
php -r '
define("CLI_SCRIPT", true);
require_once("/var/www/html/config.php");
require_once($CFG->libdir . "/completionlib.php");

$userid = 10; $courseid = 2;
$comp = completion_completion::fetch(["userid"=>$userid, "course"=>$courseid]) ?: new completion_completion(["userid"=>$userid, "course"=>$courseid]);
$comp->mark_inprogress();
echo "✅ Set course {$courseid} to IN PROGRESS for user {$userid}\\n";
'
```

---

## 5. Re-trigger completion manually

```bash
php -r '
define("CLI_SCRIPT", true);
require_once("/var/www/html/config.php");
require_once($CFG->libdir . "/completionlib.php");

$userid = 10; $courseid = 2;
$comp = new completion_completion(["userid"=>$userid, "course"=>$courseid]);
if (!$comp->is_complete()) {
    $comp->mark_complete();
    echo "✅ Marked user {$userid} complete for course {$courseid}\\n";
} else {
    echo "ℹ️ User {$userid} was already complete for course {$courseid}\\n";
}
'
```

---

## Tips

- Ensure **Completion tracking** is enabled for the course:  
  `Course → Settings → Completion tracking → Yes`
- If the course instantly re-completes after reset, loosen the completion rules.
- You can view completion data under:  
  `Course → More → Reports → Course completion`
- To reset **all users**, use `Course → More → Course reuse → Reset` (clears everyone’s progress).

---

**Author:** Navigatr Dev Tools  
**Last updated:** 2025-10-08
