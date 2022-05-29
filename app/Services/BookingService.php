<?php
namespace App\services

class BookingService{
    function getSuperAdminUsers($requestdata){
        $allJobs = Job::query();

        if (isset($requestdata['feedback']) && $requestdata['feedback'] != 'false') {
            $allJobs->where('ignore_feedback', '0');
            $allJobs->whereHas('feedback', function ($q) {
                $q->where('rating', '<=', '3');
            });
            if (isset($requestdata['count']) && $requestdata['count'] != 'false') return ['count' => $allJobs->count()];
        }

        if (isset($requestdata['id']) && $requestdata['id'] != '') {
            if (is_array($requestdata['id']))
                $allJobs->whereIn('id', $requestdata['id']);
            else
                $allJobs->where('id', $requestdata['id']);
            $requestdata = array_only($requestdata, ['id']);
        }

        if (isset($requestdata['lang']) && $requestdata['lang'] != '') {
            $allJobs->whereIn('from_language_id', $requestdata['lang']);
        }
        if (isset($requestdata['status']) && $requestdata['status'] != '') {
            $allJobs->whereIn('status', $requestdata['status']);
        }
        if (isset($requestdata['expired_at']) && $requestdata['expired_at'] != '') {
            $allJobs->where('expired_at', '>=', $requestdata['expired_at']);
        }
        if (isset($requestdata['will_expire_at']) && $requestdata['will_expire_at'] != '') {
            $allJobs->where('will_expire_at', '>=', $requestdata['will_expire_at']);
        }
        if (isset($requestdata['customer_email']) && count($requestdata['customer_email']) && $requestdata['customer_email'] != '') {
            $users = DB::table('users')->whereIn('email', $requestdata['customer_email'])->get();
            if ($users) {
                $allJobs->whereIn('user_id', collect($users)->pluck('id')->all());
            }
        }
        if (isset($requestdata['translator_email']) && count($requestdata['translator_email'])) {
            $users = DB::table('users')->whereIn('email', $requestdata['translator_email'])->get();
            if ($users) {
                $allJobIDs = DB::table('translator_job_rel')->whereNull('cancel_at')->whereIn('user_id', collect($users)->pluck('id')->all())->lists('job_id');
                $allJobs->whereIn('id', $allJobIDs);
            }
        }
        if (isset($requestdata['filter_timetype']) && $requestdata['filter_timetype'] == "created") {
            if (isset($requestdata['from']) && $requestdata['from'] != "") {
                $allJobs->where('created_at', '>=', $requestdata["from"]);
            }
            if (isset($requestdata['to']) && $requestdata['to'] != "") {
                $to = $requestdata["to"] . " 23:59:00";
                $allJobs->where('created_at', '<=', $to);
            }
            $allJobs->orderBy('created_at', 'desc');
        }
        if (isset($requestdata['filter_timetype']) && $requestdata['filter_timetype'] == "due") {
            if (isset($requestdata['from']) && $requestdata['from'] != "") {
                $allJobs->where('due', '>=', $requestdata["from"]);
            }
            if (isset($requestdata['to']) && $requestdata['to'] != "") {
                $to = $requestdata["to"] . " 23:59:00";
                $allJobs->where('due', '<=', $to);
            }
            $allJobs->orderBy('due', 'desc');
        }

        if (isset($requestdata['job_type']) && $requestdata['job_type'] != '') {
            $allJobs->whereIn('job_type', $requestdata['job_type']);
            /*$allJobs->where('jobs.job_type', '=', $requestdata['job_type']);*/
        }

        if (isset($requestdata['physical'])) {
            $allJobs->where('customer_physical_type', $requestdata['physical']);
            $allJobs->where('ignore_physical', 0);
        }

        if (isset($requestdata['phone'])) {
            $allJobs->where('customer_phone_type', $requestdata['phone']);
            if (isset($requestdata['physical']))
            $allJobs->where('ignore_physical_phone', 0);
        }

        if (isset($requestdata['flagged'])) {
            $allJobs->where('flagged', $requestdata['flagged']);
            $allJobs->where('ignore_flagged', 0);
        }

        if (isset($requestdata['distance']) && $requestdata['distance'] == 'empty') {
            $allJobs->whereDoesntHave('distance');
        }

        if (isset($requestdata['salary']) &&  $requestdata['salary'] == 'yes') {
            $allJobs->whereDoesntHave('user.salaries');
        }

        if (isset($requestdata['count']) && $requestdata['count'] == 'true') {
            $allJobs = $allJobs->count();

            return ['count' => $allJobs];
        }

        if (isset($requestdata['consumer_type']) && $requestdata['consumer_type'] != '') {
            $allJobs->whereHas('user.userMeta', function ($q) use ($requestdata) {
                $q->where('consumer_type', $requestdata['consumer_type']);
            });
        }

        if (isset($requestdata['booking_type'])) {
            if ($requestdata['booking_type'] == 'physical')
            $allJobs->where('customer_physical_type', 'yes');
            if ($requestdata['booking_type'] == 'phone')
                $allJobs->where('customer_phone_type', 'yes');
        }

        
        return $allJobs;
    }

    function getAdminUsers($requestdata)
    {
       
        $allJobs = Job::query();

        if (isset($requestdata['id']) && $requestdata['id'] != '') {
            $allJobs->where('id', $requestdata['id']);
            $requestdata = array_only($requestdata, ['id']);
        }

        if ($consumer_type == 'RWS') {
            $allJobs->where('job_type', '=', 'rws');
        } else {
            $allJobs->where('job_type', '=', 'unpaid');
        }
        if (isset($requestdata['feedback']) && $requestdata['feedback'] != 'false') {
            $allJobs->where('ignore_feedback', '0');
            $allJobs->whereHas('feedback', function ($q) {
                $q->where('rating', '<=', '3');
            });
            if (isset($requestdata['count']) && $requestdata['count'] != 'false') return ['count' => $allJobs->count()];
        }

        if (isset($requestdata['lang']) && $requestdata['lang'] != '') {
            $allJobs->whereIn('from_language_id', $requestdata['lang']);
        }
        if (isset($requestdata['status']) && $requestdata['status'] != '') {
            $allJobs->whereIn('status', $requestdata['status']);
        }
        if (isset($requestdata['job_type']) && $requestdata['job_type'] != '') {
            $allJobs->whereIn('job_type', $requestdata['job_type']);
        }
        if (isset($requestdata['customer_email']) && $requestdata['customer_email'] != '') {
            $user = DB::table('users')->where('email', $requestdata['customer_email'])->first();
            if ($user) {
                $allJobs->where('user_id', '=', $user->id);
            }
        }
        if (isset($requestdata['filter_timetype']) && $requestdata['filter_timetype'] == "created") {
            if (isset($requestdata['from']) && $requestdata['from'] != "") {
                $allJobs->where('created_at', '>=', $requestdata["from"]);
            }
            if (isset($requestdata['to']) && $requestdata['to'] != "") {
                $to = $requestdata["to"] . " 23:59:00";
                $allJobs->where('created_at', '<=', $to);
            }
            $allJobs->orderBy('created_at', 'desc');
        }
        if (isset($requestdata['filter_timetype']) && $requestdata['filter_timetype'] == "due") {
            if (isset($requestdata['from']) && $requestdata['from'] != "") {
                $allJobs->where('due', '>=', $requestdata["from"]);
            }
            if (isset($requestdata['to']) && $requestdata['to'] != "") {
                $to = $requestdata["to"] . " 23:59:00";
                $allJobs->where('due', '<=', $to);
            }
            $allJobs->orderBy('due', 'desc');
        }

        return $allJobs;
    }

    function store($data){
        $data['customer_phone_type'] = isset($data['customer_phone_type']) ? 'yes' : 'no';
        $data['customer_physical_type'] = $response['customer_physical_type'] = isset($data['customer_physical_type']) ? 'yes' : 'no';
        if ($data['immediate'] == 'yes') {
            $due_carbon = Carbon::now()->addMinute($immediatetime);
            $data['due'] = $due_carbon->format('Y-m-d H:i:s');
            $data['immediate'] = 'yes';
            $data['customer_phone_type'] = 'yes';
            $response['type'] = 'immediate';
        } else {
            $due = $data['due_date'] . " " . $data['due_time'];
            $response['type'] = 'regular';
            $due_carbon = Carbon::createFromFormat('m/d/Y H:i', $due);
            $data['due'] = $due_carbon->format('Y-m-d H:i:s');
            if ($due_carbon->isPast()) {
                $response['status'] = 'fail';
                $response['message'] = "Can't create booking in past";
                return $response;
            }
        }
        $data['gender'] = in_array('male', $data['job_for']) ? 'male' : 'female';

        if (in_array('certified', $data['job_for'])) {
            $data['certified'] = 'yes';
        } else if (in_array('certified_in_law', $data['job_for'])) {
            $data['certified'] = 'law';
        } else if (in_array('certified_in_helth', $data['job_for'])) {
            $data['certified'] = 'health';
        } else if (in_array('normal', $data['job_for'])) {
            $data['certified'] = 'normal';
            if (in_array('certified', $data['job_for'])) {
                $data['certified'] = 'both';
            } else if (in_array('certified_in_law', $data['job_for'])) {
                $data['certified'] = 'n_law';
            } else if (in_array('certified_in_helth', $data['job_for'])) {
                $data['certified'] = 'n_health';
            }
        }
        $job_type = ['rwsconsumer' => 'rws', 'ngo' => 'unpaid', 'paid' => 'paid'];
        $data['job_type'] = $job_type[$consumer_type];

        $data['b_created_at'] = date('Y-m-d H:i:s');
        if (isset($due))
            $data['will_expire_at'] = TeHelper::willExpireAt($due, $data['b_created_at']);
        $data['by_admin'] = isset($data['by_admin']) ? $data['by_admin'] : 'no';
        $job = $user->jobs()->create($data);


        $data['job_for'] = array();
        if ($job->gender != null && $job->gender == 'male') {
            $data['job_for'][] = 'Man';
        } else if ($job->gender != null && $job->gender == 'female') {
            $data['job_for'][] = 'Kvinna';
        }

        if ($job->certified != null) {
            if ($job->certified == 'both') {
                $data['job_for'][] = 'normal';
                $data['job_for'][] = 'certified';
            } else if ($job->certified == 'yes') {
                $data['job_for'][] = 'certified';
            } else {
                $data['job_for'][] = $job->certified;
            }
        }
        $data['customer_town'] = $user->userMeta->city;
        $data['customer_type'] = $user->userMeta->customer_type;

        return $response;
    }
}

