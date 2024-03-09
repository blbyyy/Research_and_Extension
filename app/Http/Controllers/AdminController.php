<?php

namespace App\Http\Controllers;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Mail;
use App\Mail\CertificationComplete;
use App\Mail\CertificationPassed;
use RealRashid\SweetAlert\Facades\Alert;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Spatie\Svg\Converter;
use Spatie\Browsershot\Browsershot;
use App\Models\Student;
use App\Models\Staff;
use App\Models\Faculty;
use App\Models\User;
use App\Models\Certificate;
use App\Models\Announcement;
use App\Models\RequestingForm;
use App\Models\Files;
use App\Models\Research;
use App\Http\Redirect;
use setasign\Fpdi\Fpdi;
use Dompdf\Dompdf;
use Imagick;
use TCPDF;
use FPDF;
use View;
use DB;
use File;
use Auth;

class AdminController extends Controller
{

    public function dashboard()
    {
        $usersCount = DB::table('users')->count();

        $rolesCount = DB::table('users')
        ->select('role', DB::raw('count(*) as count'))
        ->groupBy('role')
        ->get();

        $applicationsCount = DB::table('requestingform')
        ->select('status', DB::raw('count(*) as count'))
        ->groupBy('status')
        ->get();

        $thesisTypeCount = DB::table('requestingform')
        ->select('thesis_type', DB::raw('count(*) as count'))
        ->groupBy('thesis_type')
        ->get();

        $courseCount = DB::table('requestingform')
        ->select('course', DB::raw('count(*) as count'))
        ->groupBy('course')
        ->get();

        $researchDepartmentCount = DB::table('research_list')
        ->select('department', DB::raw('count(*) as count'))
        ->groupBy('department')
        ->get();

        $researchCourseCount = DB::table('research_list')
        ->select('course', DB::raw('count(*) as count'))
        ->groupBy('course')
        ->get();

        $studentCount = DB::table('users')->where('role', 'Student')->count();

        $staffCount = DB::table('users')->where('role', 'Staff')->count();

        $facultyCount = DB::table('users')->where('role', 'Faculty')->count();

        $applicationCount = DB::table('requestingform')->count();
        
        $pendingCount = DB::table('requestingform')
            ->join('files','files.id','requestingform.research_id')
            ->where('requestingform.status', '=', 'Pending')
            ->count();

        $passedCount = DB::table('requestingform')
            ->join('files','files.id','requestingform.research_id')
            ->where('requestingform.status', '=', 'Passed')
            ->count();
        
        $returnedCount = DB::table('requestingform')
            ->join('files','files.id','requestingform.research_id')
            ->where('requestingform.status', '=', 'Returned')
            ->count();

        $admin = DB::table('staff')
            ->join('users','users.id','staff.user_id')
            ->select('staff.*','users.*')
            ->where('user_id',Auth::id())
            ->first();

        $researchCount = DB::table('research_list')->count();
        $eaadResearchCount = DB::table('research_list')->where('department', 'EAAD')->count();
        $maadResearchCount = DB::table('research_list')->where('department', 'MAAD')->count();
        $basdResearchCount = DB::table('research_list')->where('department', 'BASD')->count();
        $caadResearchCount = DB::table('research_list')->where('department', 'CAAD')->count();
        
        $adminNotifCount = DB::table('notifications')
            ->where('type', 'Admin Notification')
            ->count();

        $adminNotification = DB::table('notifications')
            ->where('type', 'Admin Notification')
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();

        return View::make('admin.dashboard',compact('adminNotifCount','adminNotification','usersCount','studentCount','staffCount','facultyCount','applicationCount','admin','pendingCount','passedCount','returnedCount','eaadResearchCount','maadResearchCount','caadResearchCount','basdResearchCount','researchCount','rolesCount','applicationsCount','thesisTypeCount','courseCount','researchDepartmentCount','researchCourseCount'));
    }

    public function administration()
    {
        $admin = DB::table('staff')
            ->join('users','users.id','staff.user_id')
            ->select('staff.*','users.*')
            ->where('user_id',Auth::id())
            ->first();

        $adminlist = DB::table('staff')  
            ->join('users', 'users.id', 'staff.user_id')  
            ->select('staff.*', 'users.id as userid', 'users.role')  
            ->get(); 

        $adminNotifCount = DB::table('notifications')
            ->where('type', 'Admin Notification')
            ->count();

        $adminNotification = DB::table('notifications')
            ->where('type', 'Admin Notification')
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();
        
        return View::make('admin.administration',compact('admin','adminlist','adminNotifCount','adminNotification'));
    }

    public function addAdministration(Request $request)
    {
            $users = new User();
            $users->fname = $request->admin_fname; 
            $users->lname = $request->admin_lname;
            $users->mname = $request->admin_mname; 
            $users->role = $request->admin_role; 
            $users->email = $request->admin_email;
            $users->password = bcrypt($request->admin_password);
            $users->save();
            $lastid = DB::getPdo()->lastInsertId();

            $staff = new Staff();
            $staff->fname = $request->admin_fname;
            $staff->lname = $request->admin_lname;
            $staff->mname = $request->admin_mname;
            $staff->position = $request->admin_position;
            $staff->designation = $request->admin_designation;
            $staff->tup_id = $request->admin_id;
            $staff->email = $request->admin_email;
            $staff->gender = $request->admin_gender;
            $staff->phone = $request->admin_phone;
            $staff->address = $request->admin_address;
            $staff->birthdate = $request->admin_birthdate;
            $staff->user_id = $lastid;
            $staff->save();
                
            return redirect()->to('/administration')->with('success', 'Administrator Added');
    }

    public function editAdministration($id)
    {
        $staff = Staff::find($id);
        return response()->json($staff);
    }

    public function updateAdministration(Request $request, $id)
    {
        $staff = Staff::find($id);
        $staff->fname = $request->fname;
        $staff->lname = $request->lname;
        $staff->mname = $request->mname;
        $staff->position = $request->position;
        $staff->designation = $request->designation;
        $staff->tup_id = $request->staffid;
        $staff->email = $request->email;
        $staff->gender = $request->gender;
        $staff->phone = $request->phone;
        $staff->address = $request->address;
        $staff->birthdate = $request->birthdate;
        $staff->save();

        $user_id = DB::table('staff')
        ->join('users','users.id','staff.user_id')
        ->select('users.id')
        ->where('staff.id',$id)
        ->first();

        $user = User::find($user_id->id);
        $user->fname = $request->fname;
        $user->lname = $request->lname;
        $user->mname = $request->mname;
        $user->email = $request->email;
        $user->save();

        return response()->json(["staff" => $staff, "user" => $user],201);
    }

    public function editAdministrationRole($id)
    {
        $admin = DB::table('staff')
        ->join('users','users.id','staff.user_id')
        ->select('staff.*','users.id as userid','users.role')
        ->where('staff.id', $id)
        ->first();

        return response()->json($admin);
    }

    public function updateAdministrationRole(Request $request, $id)
    {
        $user = User::find($request->roleId);
        $user->role = $request->role;
        $user->save();

        return response()->json($user);
    }

    public function deleteAdministration(string $id)
    {
        $staff = Staff::findOrFail($id);
        $staff->delete();
        $data = array('success' =>'deleted','code'=>'200');
        return response()->json($data);
    }
   
    public function showannouncement()
    {
        $student = DB::table('students')
        ->join('users','users.id','students.user_id')
        ->select('students.*','users.*')
        ->where('user_id',Auth::id())
        ->first();
    
        $staff = DB::table('staff')
        ->join('users','users.id','staff.user_id')
        ->select('staff.*','users.*')
        ->where('user_id',Auth::id())
        ->first();

        $faculty = DB::table('faculty')
        ->join('users','users.id','faculty.user_id')
        ->select('faculty.*','users.*')
        ->where('user_id',Auth::id())
        ->first();

        $admin = DB::table('staff')
        ->join('users','users.id','staff.user_id')
        ->select('staff.*','users.*')
        ->where('user_id',Auth::id())
        ->first();

        $announcements = DB::table('announcements')
        ->join('announcementsphoto', 'announcementsphoto.announcements_id', 'announcements.id')
        ->join('users', 'announcements.user_id', 'users.id')
        ->select(
            'users.fname',
            'users.lname',
            'users.mname',
            'users.role',
            'announcementsphoto.id as photo_id', 
            'announcements.id as announcement_id', 
            'announcements.title', 
            'announcements.content', 
            'announcementsphoto.img_path', 
            DB::raw('TIME(announcements.created_at) as created_time')
        )
        ->where('viewer', 'Students') 
        ->orderBy('announcements.id') 
        ->get()
        ->groupBy('announcement_id');

        return View::make('layouts.homepage',compact('admin','student','staff','faculty','announcements'));
    }

    public function announcement_img_upload($filename)
    {
        $photo = array('photo' => $filename);
        $destinationPath = public_path().'/images'; 
        $original_filename = time().$filename->getClientOriginalName();
        $extension = $filename->getClientOriginalExtension(); 
        $filename->move($destinationPath, $original_filename); 
    }

    public function add_announcements(Request $request)
    {  
            $announcment = new Announcement();
            $announcment->title = $request->title; 
            $announcment->content = $request->content;
            $announcment->viewer = $request->viewer;
            $announcment->user_id = Auth::id();
            $announcment->save();
            $announcment_id = DB::getPdo()->lastInsertId();

                $files = $request->file('img_path');
                foreach ($files as $file) {
                $this->announcement_img_upload($file);
                $multi['img_path']=time().$file->getClientOriginalName();
                $multi['announcements_id'] = $announcment_id ;
                DB::table('announcementsphoto')->insert($multi);
        }

        return redirect()->to('/announcements')->with('success', 'Announcement was successfully created');
    }

    public function profile($id)
    {
        $admin = DB::table('staff')
        ->join('users','users.id','staff.user_id')
        ->select('staff.*','users.*')
        ->where('user_id',Auth::id())
        ->first();

        return View::make('admin.profile',compact('admin'));
    }

    public function updateprofile(Request $request, $id)
    {
        $staff_id = DB::table('staff')
        ->select('staff.id')
        ->where('user_id',Auth::id())
        ->first();

        $staff = Staff::find($staff_id->id);
        $staff->fname = $request->fname;
        $staff->lname = $request->lname;
        $staff->mname = $request->mname;
        $staff->profession = $request->profession;
        $staff->staff_id = $request->staff_id;
        $staff->gender = $request->gender;
        $staff->phone = $request->phone;
        $staff->address = $request->address;
        $staff->birthdate = $request->birthdate;

        $user = User::find(Auth::id());
        $user->fname = $request->fname;
        $user->lname = $request->lname;
        $user->mname = $request->mname;
        $user->save();

        Alert::success('Success', 'Profile was successfully updated');

        return redirect()->to('/Admin/Profile/{id}')->with('success', 'Profile was successfully updated');
    }

    public function changeavatar(Request $request)
    {
        $admin = DB::table('staff')
        ->select('staff.id')
        ->where('user_id',Auth::id())
        ->first();

        $admin = Staff::find($admin->id);
        $files = $request->file('avatar');
        $admin->avatar = 'images/'.time().'-'.$files->getClientOriginalName();

        $admin->save();

        $data = array('status' => 'saved');
        Storage::put('public/images/'.time().'-'.$files->getClientOriginalName(), file_get_contents($files));
        $admin->save();

        Alert::success('Success', 'Avatar changed successfully!');

        return redirect()->to('/Admin/Profile/{id}')->with('success', 'Avatar changed successfully.');
    }
    
    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'newpassword' => 'required|min:8',
            'renewpassword' => 'required|same:newpassword',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            Alert::error('Error', 'Current password is incorrect.');
            return redirect()->back()->with('error', 'Current password is incorrect.');
        }

        $user->update([
            'password' => Hash::make($request->newpassword),
        ]);

        Alert::success('Success', 'Password changed successfully!');
        return redirect()->to('/Admin/Profile/{id}')->with('success', 'Password changed successfully.');
    }

    public function studentlist()
    {
        $admin = DB::table('staff')
        ->join('users','users.id','staff.user_id')
        ->select('staff.*','users.*')
        ->where('user_id',Auth::id())
        ->first();

        $studentlist = Student::orderBy('id')->get();
        
        $adminNotifCount = DB::table('notifications')
            ->where('type', 'Admin Notification')
            ->count();

        $adminNotification = DB::table('notifications')
            ->where('type', 'Admin Notification')
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();
        
        return View::make('admin.studentlist',compact('studentlist','admin','adminNotifCount','adminNotification'));
    }

    public function addstudent(Request $request)
    {
            $users = new User();
            $users->fname = $request->fname; 
            $users->lname = $request->lname;
            $users->mname = $request->mname; 
            $users->role = 'Student'; 
            $users->email = $request->email;
            $users->password = bcrypt($request->password);
            $users->save();
            $lastid = DB::getPdo()->lastInsertId();

            $student = new Student();
            $student->fname = $request->fname; 
            $student->lname = $request->lname;
            $student->mname = $request->mname; 
            $student->email = $request->email;
            $student->college = $request->college; 
            $student->course = $request->course;
            $student->student_id = $request->student_id; 
            $student->phone = $request->phone;
            $student->address = $request->address; 
            $student->gender = $request->gender;
            $student->birthdate = $request->birthdate; 
            $student->user_id = $lastid; 
            $student->save();
            
            return redirect()->to('/studentlist')->with('success', 'Student Added');
    }

    public function showstudentinfo($id)
    {
        $student = Student::find($id);
        return response()->json($student);
    }

    public function editstudentinfo($id)
    {
        $student = Student::find($id);
        return response()->json($student);
    }

    public function updatestudentinfo(Request $request, $id)
    {
        $student = Student::find($id);
        $student->fname = $request->fname;
        $student->lname = $request->lname;
        $student->mname = $request->mname;
        $student->college = $request->college;
        $student->course = $request->course;
        $student->tup_id = $request->sid;
        $student->email = $request->email;
        $student->gender = $request->gender;
        $student->phone = $request->phone;
        $student->address = $request->address;
        $student->birthdate = $request->birthdate;
        $student->save();

        $user_id = DB::table('students')
        ->join('users','users.id','students.user_id')
        ->select('users.id')
        ->where('students.id',$id)
        ->first();

        $user = User::find($user_id->id);
        $user->fname = $request->fname;
        $user->lname = $request->lname;
        $user->mname = $request->mname;
        $user->email = $request->email;
        $user->save();

        return response()->json(["student" => $student, "user" => $user],201);
    }

    public function deletestudentinfo(string $id)
    {
        $student = Student::findOrFail($id);
        $student->delete();
        $data = array('success' =>'deleted','code'=>'200');
        return response()->json($data);
    }

    public function stafflist()
    {
        $admin = DB::table('staff')
        ->join('users','users.id','staff.user_id')
        ->select('staff.*','users.*')
        ->where('user_id',Auth::id())
        ->first();

        $stafflist = Staff::orderBy('id')->get();

        $adminNotifCount = DB::table('notifications')
            ->where('type', 'Admin Notification')
            ->count();

        $adminNotification = DB::table('notifications')
            ->where('type', 'Admin Notification')
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();

        return View::make('admin.stafflist',compact('stafflist','admin','adminNotifCount','adminNotification'));
    }

    public function addstaff(Request $request)
    {
            $users = new User();
            $users->fname = $request->fname; 
            $users->lname = $request->lname;
            $users->mname = $request->mname; 
            $users->role = 'Staff'; 
            $users->email = $request->email;
            $users->password = bcrypt($request->password);
            $users->save();
            $lastid = DB::getPdo()->lastInsertId();

            $staff = new Staff();
            $staff->fname = $request->fname;
            $staff->lname = $request->lname;
            $staff->mname = $request->mname;
            $staff->position = $request->position;
            $staff->designation = $request->designation;
            $staff->staff_id = $request->staff_id;
            $staff->email = $request->email;
            $staff->gender = $request->gender;
            $staff->phone = $request->phone;
            $staff->address = $request->address;
            $staff->birthdate = $request->birthdate;
            $staff->save();
                
            return redirect()->to('/stafflist');
    }

    public function showstaffinfo($id)
    {
        $staff = Staff::find($id);
        return response()->json($staff);
    }

    public function editstaffinfo($id)
    {
        $staff = Staff::find($id);
        return response()->json($staff);
    }

    public function updatestaffinfo(Request $request, $id)
    {
        $staff = Staff::find($id);
        $staff->fname = $request->fname;
        $staff->lname = $request->lname;
        $staff->mname = $request->mname;
        $staff->position = $request->position;
        $staff->designation = $request->designation;
        $staff->tup_id = $request->staffid;
        $staff->email = $request->email;
        $staff->gender = $request->gender;
        $staff->phone = $request->phone;
        $staff->address = $request->address;
        $staff->birthdate = $request->birthdate;
        $staff->save();

        $user_id = DB::table('staff')
        ->join('users','users.id','staff.user_id')
        ->select('users.id')
        ->where('staff.id',$id)
        ->first();

        $user = User::find($user_id->id);
        $user->fname = $request->fname;
        $user->lname = $request->lname;
        $user->mname = $request->mname;
        $user->email = $request->email;
        $user->save();

        return response()->json(["staff" => $staff, "user" => $user]);
    }

    public function deletestaffinfo(string $id)
    {
        $staff = Staff::findOrFail($id);
        $staff->delete();
        $data = array('success' =>'deleted','code'=>'200');
        return response()->json($data);
    }

    public function facultymemberlist()
    {
        $admin = DB::table('staff')
        ->join('users','users.id','staff.user_id')
        ->select('staff.*','users.*')
        ->where('user_id',Auth::id())
        ->first();

        $facultylist = Faculty::orderBy('id')->get();

        $adminNotifCount = DB::table('notifications')
            ->where('type', 'Admin Notification')
            ->count();

        $adminNotification = DB::table('notifications')
            ->where('type', 'Admin Notification')
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();

        return View::make('admin.facultylist',compact('facultylist','admin','adminNotifCount','adminNotification'));
    }

    public function addfaculty(Request $request)
    {
            $users = new User();
            $users->fname = $request->fname; 
            $users->lname = $request->lname;
            $users->mname = $request->mname; 
            $users->role = 'Faculty'; 
            $users->email = $request->email;
            $users->password = bcrypt($request->password);
            $users->save();
            $lastid = DB::getPdo()->lastInsertId();

            $faculty = new Faculty();
            $faculty->fname = $request->fname;
            $faculty->lname = $request->lname;
            $faculty->mname = $request->mname;
            $faculty->position = $request->position;
            $faculty->designation = $request->designation;
            $faculty->department = $request->department;
            $faculty->tup_id = $request->tup_id;
            $faculty->email = $request->email;
            $faculty->gender = $request->gender;
            $faculty->phone = $request->phone;
            $faculty->address = $request->address;
            $faculty->birthdate = $request->birthdate;
            $faculty->save();
                
            return redirect()->to('/facultylist');
    }

    public function showfacultyinfo($id)
    {
        $faculty = Faculty::find($id);
        return response()->json($faculty);
    }

    public function editfacultyinfo($id)
    {
        $faculty = Faculty::find($id);
        return response()->json($faculty);
    }

    public function updatefacultyinfo(Request $request, $id)
    {
        $faculty = Faculty::find($id);
        $faculty->fname = $request->fname;
        $faculty->lname = $request->lname;
        $faculty->mname = $request->mname;
        $faculty->department = $request->department;
        $faculty->tup_id = $request->staffid;
        $faculty->email = $request->email;
        $faculty->gender = $request->gender;
        $faculty->phone = $request->phone;
        $faculty->address = $request->address;
        $faculty->birthdate = $request->birthdate;
        $faculty->save();

        $user_id = DB::table('staff')
        ->join('users','users.id','staff.user_id')
        ->select('users.id')
        ->where('staff.id',$id)
        ->first();

        $user = User::find($user_id->id);
        $user->fname = $request->fname;
        $user->lname = $request->lname;
        $user->mname = $request->mname;
        $user->email = $request->email;
        $user->save();

        return response()->json(["faculty" => $faculty, "user" => $user]);
    }

    public function deletefacultyinfo(string $id)
    {
        $faculty = Faculty::findOrFail($id);
        $faculty->delete();
        $data = array('success' =>'deleted','code'=>'200');
        return response()->json($data);
    }

    public function admin_certification($id)
    {
        $specificData = DB::table('requestingform')
        ->join('files', 'files.id', 'requestingform.research_id')
        ->join('users', 'users.id', 'requestingform.user_id')
        ->select('requestingform.*', 'files.*')
        ->where('requestingform.id', $id)
        ->first();

        return response()->json($specificData);

    }
    
    public function certification(Request $request, $id)
    {
        $request->validate([
            'certification_file' => 'nullable|mimes:pdf|max:2048', 
        ]);

        $fileId = DB::table('files')
        ->join('requestingform','requestingform.research_id','files.id')
        ->select('requestingform.*','files.*')
        ->where('requestingform.id',$id)
        ->first();

        $staff = DB::table('staff')
        ->join('users','users.id','staff.user_id')
        ->select('staff.*','users.*')
        ->where('user_id',Auth::id())
        ->first();

        $specialist = $staff->fname .' '. $staff->mname .' '. $staff->lname;
        $userEmail = $fileId->tup_mail;
        $userName = $fileId->requestor_name;
        
        if ($request->status === 'Passed') {

            $certificateReCount = DB::table('certificates')->count();
            $certificateCount = ++$certificateReCount;
            $currentYearMonth = date('Ym');

            if ($certificateCount >= 1 && $certificateCount <= 9) {
                $qrCodeName = $currentYearMonth . 0 . 0 . 0 . $certificateCount;
            } else if ($certificateCount >= 10 && $certificateCount <= 99) {
                $qrCodeName = $currentYearMonth . 0 . 0 . $certificateCount;
            } else if ($certificateCount >= 100 && $certificateCount <= 999) {
                $qrCodeName = $currentYearMonth . 0 . $certificateCount;
            } else if ($certificateCount >= 1000) {
                $qrCodeName = $currentYearMonth . $certificateCount;
            }
            
            $cert = new Certificate();
            $cert->control_id = $qrCodeName;
            $cert->save();
            $lastId = DB::getPdo()->lastInsertId();

            $form = RequestingForm::find($id);
            $form->status = $request->status;
            $form->simmilarity_percentage_results = $request->simmilarity_percentage_results;
            $form->research_specialist = $specialist;
            $form->research_staff = $specialist;
            $form->date_processing_end = now();
            $form->remarks = 'Your certificate is ready to be picked up. Please visit the R&E Services Office to collect it.';
            $form->certificate_id = $lastId;
            $form->save();

            $file = Files::find($fileId->id);
            $file->file_status = $request->status;
            $file->save();

            $controlId = DB::table('certificates')
            ->where('id', $lastId)
            ->value('control_id');

            $researchTitle = DB::table('requestingform')
            ->join('files','files.id','requestingform.research_id')
            ->where('certificate_id', $lastId)
            ->value('research_title');

            $latestFile = DB::table('files')
            ->join('requestingform','requestingform.research_id','files.id')
            ->select('requestingform.*','files.*')
            ->where('requestingform.id',$id)
            ->first();

            $certificate = 'http://localhost:8000/certificate/' . $qrCodeName;

            $date = \Carbon\Carbon::parse($latestFile->date_processing_end)->format('F d, Y');

            $qrCodePath = public_path("uploads/certificate/image/{$qrCodeName}.png");
            QrCode::format('png')->size(300)->generate($certificate, $qrCodePath);

            $existingPdfPath = public_path("uploads/certificate/CertificateFormat.pdf");

            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($existingPdfPath);

            for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
                $pdf->AddPage();
      
                $templateId = $pdf->importPage($pageNumber);
                $pdf->useTemplate($templateId);
           
                $pdf->SetFont('Arial', '', 12);
                $pdf->SetXY(10, 65); 
                $pdf->MultiCell(0, 10, ' This is to certify that the manuscript entitled ', 0, 'C');
      
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetXY(10, 75); 
                $pdf->MultiCell(0, 10, $latestFile->research_title, 0, 'C');
      
                $pdf->SetFont('Arial', '', 12);
                $pdf->SetXY(10, 115); 
                $pdf->MultiCell(0, 10, ' authored by ', 0, 'C');
      
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetXY(10, 130); 
                $pdf->MultiCell(0, 10, $latestFile->researchers_name1, 0, 'C');
                $pdf->SetXY(10, 135); 
                $pdf->MultiCell(0, 10, $latestFile->researchers_name2, 0, 'C');
                $pdf->SetXY(10, 140); 
                $pdf->MultiCell(0, 10, $latestFile->researchers_name3, 0, 'C');
      
                $pdf->SetFont('Arial', '', 12);
                $pdf->SetXY(10, 150); 
                $pdf->MultiCell(0, 5, 'has been subjected to similarity check on ' . $date . 
                ' using Turnitin with generated similarity index of ' . $latestFile->simmilarity_percentage_results . '%', 0, 'C');
                $pdf->SetXY(10, 170); 
                $pdf->MultiCell(0, 10, ' Processed by: ', 0, 'C');
      
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetXY(10, 180); 
                $pdf->MultiCell(0, 10, $latestFile->research_staff, 0, 'C');
      
                $pdf->SetFont('Arial', 'I', 12);
                $pdf->SetXY(10, 185); 
                $pdf->MultiCell(0, 10, ' Head of Research & Development Services ', 0, 'C');
      
                $pdf->SetFont('Arial', '', 12);
                $pdf->SetXY(10, 200); 
                $pdf->MultiCell(0, 10, ' Certified Correct by: ', 0, 'C');
      
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetXY(10, 210); 
                $pdf->MultiCell(0, 10, ' Laarnie D. Macapagal, DMS ', 0, 'C');
      
                $pdf->SetFont('Arial', 'I', 12);
                $pdf->SetXY(10, 215); 
                $pdf->MultiCell(0, 10, ' Assistant Director of Research & Extension Services ', 0, 'C');
      
                $pdf->Image($qrCodePath, 18, 230, 20, 0, 'PNG');
                $pdf->SetFont('Arial', 'I', 11);
                $pdf->SetXY(16, 248); 
                $pdf->MultiCell(0, 10, $qrCodeName, 0, 0);
              
            }

            $pdf->Output('F', public_path("uploads/certificate/pdf/{$qrCodeName}.pdf"));
        
            $data = [
                'researchTitle' => $researchTitle,
                'controlId' => $controlId,
                'userName' => $userName,
                'status' => $request->status,
                'percentage_results' => $request->simmilarity_percentage_results,
            ];
        
            // Mail::to($userEmail)->send(new CertificationPassed($data));
        }else {  

            $researchTitle = DB::table('requestingform')
            ->join('files','files.id','requestingform.research_id')
            ->where('requestingform.id', $id)
            ->value('research_title');

            $form = RequestingForm::find($id);
            $form->status = $request->status;
            $form->simmilarity_percentage_results = $request->simmilarity_percentage_results;
            $form->date_processing_end = now();
            $form->research_specialist = $specialist;
            $form->research_staff = $specialist;
            $form->remarks = 'The requirements for certifying your application as passed have not been met.';
            $form->save();

            $file = Files::find($fileId->id);
            $file->file_status = $request->status;
            $file->save();

            $data = [
                'researchTitle' => $researchTitle,
                'userName' => $userName,
                'status' => $request->status,
                'percentage_results' => $request->simmilarity_percentage_results,
            ];
        
            // Mail::to($userEmail)->send(new CertificationComplete($data));
        }

        return response()->json($file);
    }

    public function certificate_tracking()
    {
        $admin = DB::table('staff')
            ->join('users','users.id','staff.user_id')
            ->select('staff.*','users.*')
            ->where('user_id',Auth::id())
            ->first();

        $staff = DB::table('staff')
            ->join('users','users.id','staff.user_id')
            ->select('staff.*','users.*')
            ->where('user_id',Auth::id())
            ->first();

        $certificates = DB::table('certificates')  
            ->join('requestingform', 'requestingform.certificate_id', 'certificates.id')
            ->join('files', 'files.id', 'requestingform.research_id')  
            ->select(
                'requestingform.*', 
                'files.*', 
                'certificates.id as certId', 
                'certificates.control_id',
                'certificates.certificate_file')  
            ->get();
        
        $adminNotifCount = DB::table('notifications')
            ->where('type', 'Admin Notification')
            ->count();

        $adminNotification = DB::table('notifications')
            ->where('type', 'Admin Notification')
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();

        return View::make('certificate.tracking',compact('admin','certificates','staff','adminNotifCount','adminNotification'));
    }

    public function show_certificate($certId)
    {
        $checker = DB::table('requestingform')
            ->where('certificate_id', $certId)
            ->value('requestor_type');

        if ($checker === 'Faculty') {
            $specificData = DB::table('requestingform')
            ->join('files', 'files.id', 'requestingform.research_id')
            ->join('users', 'users.id', 'requestingform.user_id')
            ->leftJoin('certificates', 'certificates.id', 'requestingform.certificate_id')
            ->select(
                'requestingform.*', 
                'files.*',
                'certificates.certificate_file',
                'certificates.control_id',)
            ->where('requestingform.certificate_id', $certId)
            ->first();
        } else {
            $specificData = DB::table('requestingform')
            ->join('files', 'files.id', 'requestingform.research_id')
            ->join('users', 'users.id', 'requestingform.user_id')
            ->join('faculty as technical_adviser', 'technical_adviser.id', '=', 'requestingform.technicalAdviser_id')
            ->join('faculty as subject_adviser', 'subject_adviser.id', '=', 'requestingform.subjectAdviser_id')
            ->leftJoin('certificates', 'certificates.id', 'requestingform.certificate_id')
            ->select(
                'requestingform.*', 
                'files.*',
                'certificates.certificate_file',
                'certificates.control_id',
                'technical_adviser.id as technical_adviser_id',
                'subject_adviser.id as subject_adviser_id',
                DB::raw("CONCAT(technical_adviser.fname, ' ', technical_adviser.lname, ' ', technical_adviser.mname) as TechnicalAdviserName"),
                DB::raw("CONCAT(subject_adviser.fname, ' ', subject_adviser.lname, ' ', subject_adviser.mname) as SubjectAdviserName"))
            ->where('requestingform.certificate_id', $certId)
            ->first();
        }

        return response()->json($specificData);
    }

    public function fetchSpecificCertificate(Request $request)
    {
        $admin = DB::table('staff')
            ->join('users','users.id','staff.user_id')
            ->select('staff.*','users.*')
            ->where('user_id',Auth::id())
            ->first();

        $controlNumber = $request->input('controlId');
        
        $certificates = DB::table('certificates')  
            ->join('requestingform', 'requestingform.certificate_id', 'certificates.id')
            ->join('files', 'files.id', 'requestingform.research_id')  
            ->select(
                'requestingform.*', 
                'files.*', 
                'certificates.id as certId', 
                'certificates.control_id',
                'certificates.certificate_file') 
            ->where('certificates.control_id', 'like', "%$controlNumber%") 
            ->get(); 

            return View::make('certificate.tracking',compact('admin','certificates'));

    }

    public function userlist()
    {
        $admin = DB::table('staff')
        ->join('users','users.id','staff.user_id')
        ->select('staff.*','users.*')
        ->where('user_id',Auth::id())
        ->first();

        $users = User::orderBy('id')
            ->select(
                'users.id as user_id',
                'users.fname',
                'users.mname',
                'users.lname',
                'users.email',
                'users.role',
            )
            ->get();

        return View::make('admin.userslist',compact('users','admin'));
    }

    public function selectedSpecificRole(Request $request)
    {
        $admin = DB::table('staff')
        ->join('users','users.id','staff.user_id')
        ->select('staff.*','users.*')
        ->where('user_id',Auth::id())
        ->first();

        if ($request->userRole === 'Student') {
            $users = DB::table('users')
            ->join('students', 'users.id', '=', 'students.user_id')
            ->select('users.*','students.*')
            ->where('users.role', 'Student')
            ->get();
        } elseif ($request->userRole === 'Faculty') {
            $users = DB::table('users')
            ->join('faculty', 'users.id', '=', 'faculty.user_id')
            ->select('users.*','faculty.*')
            ->where('users.role', 'Faculty')
            ->get();
        } elseif ($request->userRole === 'Staff') {
            $users = DB::table('users')
            ->join('staff', 'users.id', '=', 'staff.user_id')
            ->select('users.*','staff.*')
            ->where('users.role', 'Staff')
            ->get();
        } elseif ($request->userRole === 'All') {
            $users = User::orderBy('id')->with(['student', 'faculty', 'staff'])->get();
        }

        return View::make('admin.userslist',compact('users','admin'));
    }

    public function showUserlistInfo($id)
    {
        $role = User::orderBy('id')
            ->where('id', $id)
            ->with(['student', 'faculty', 'staff'])
            ->value('role');

            if ($role === 'Student') {
                $users = DB::table('users')
                ->join('students','users.id','students.user_id')
                ->select('students.*','users.*')
                ->where('users.id', $id)
                ->first();
            } elseif ($role === 'Staff') {
                $users = DB::table('users')
                ->join('staff','users.id','staff.user_id')
                ->select('staff.*','users.*')
                ->where('users.id', $id)
                ->first();
            } elseif ($role === 'Faculty') {
                $users = DB::table('users')
                ->join('faculty','users.id','faculty.user_id')
                ->join('departments','departments.id','faculty.department_id')
                ->select('faculty.*','users.*','departments.id as deptId','departments.department_name')
                ->where('users.id', $id)
                ->first();
            } elseif ($role === 'Admin') {
                $users = DB::table('users')
                ->join('staff','users.id','staff.user_id')
                ->select('staff.*','users.*')
                ->where('users.id', $id)
                ->first();
            }

        return response()->json($users);
    }

    public function updateUserInfo(Request $request, $id)
    {
        $role = User::orderBy('id')
            ->where('id', $id)
            ->with(['student', 'faculty', 'staff'])
            ->value('role');

            if ($role === 'Student') {
                $user = Student::find($id);
                $user->fname = $request->fname;
                $user->lname = $request->lname;
                $user->mname = $request->mname;
                $user->college = $request->college;
                $user->course = $request->course;
                $user->tup_id = $request->tup_id;
                $user->email = $request->email;
                $user->gender = $request->gender;
                $user->phone = $request->phone;
                $user->address = $request->address;
                $user->birthdate = $request->birthdate;
                $user->save();

                $userId = DB::table('students')
                ->join('users','users.id','students.user_id')
                ->select('users.id')
                ->where('students.id',$id)
                ->first();

                $users = User::find($userId->id);
                $users->fname = $request->fname;
                $users->lname = $request->lname;
                $users->mname = $request->mname;
                $users->email = $request->email;
                $users->save();
            } elseif ($role === 'Staff') {
                $user = Staff::find($id);
                $user->fname = $request->fname;
                $user->lname = $request->lname;
                $user->mname = $request->mname;
                $user->position = $request->position;
                $user->designation = $request->designation;
                $user->tup_id = $request->tup_id;
                $user->email = $request->email;
                $user->gender = $request->gender;
                $user->phone = $request->phone;
                $user->address = $request->address;
                $user->birthdate = $request->birthdate;
                $user->save();

                $userId = DB::table('staff')
                ->join('users','users.id','staff.user_id')
                ->select('users.id')
                ->where('staff.id',$id)
                ->first();

                $users = User::find($userId->id);
                $users->fname = $request->fname;
                $users->lname = $request->lname;
                $users->mname = $request->mname;
                $users->email = $request->email;
                $users->save();
            } elseif ($role === 'Faculty') {
                $user = Faculty::find($id);
                $user->fname = $request->fname;
                $user->lname = $request->lname;
                $user->mname = $request->mname;
                $user->position = $request->position;
                $user->designation = $request->designation;
                $user->tup_id = $request->tup_id;
                $user->email = $request->email;
                $user->gender = $request->gender;
                $user->phone = $request->phone;
                $user->address = $request->address;
                $user->birthdate = $request->birthdate;
                $user->save();

                $userId = DB::table('faculty')
                ->join('users','users.id','faculty.user_id')
                ->select('users.id')
                ->where('faculty.id',$id)
                ->first();

                $users = User::find($userId->id);
                $users->fname = $request->fname;
                $users->lname = $request->lname;
                $users->mname = $request->mname;
                $users->email = $request->email;
                $users->save();

            } elseif ($role === 'Admin') {
                $user = Staff::find($id);
                $user->fname = $request->fname;
                $user->lname = $request->lname;
                $user->mname = $request->mname;
                $user->position = $request->position;
                $user->designation = $request->designation;
                $user->tup_id = $request->tup_id;
                $user->email = $request->email;
                $user->gender = $request->gender;
                $user->phone = $request->phone;
                $user->address = $request->address;
                $user->birthdate = $request->birthdate;
                $user->save();

                $userId = DB::table('staff')
                ->join('users','users.id','staff.user_id')
                ->select('users.id')
                ->where('staff.id',$id)
                ->first();

                $users = User::find($userId->id);
                $users->fname = $request->fname;
                $users->lname = $request->lname;
                $users->mname = $request->mname;
                $users->email = $request->email;
                $users->save();
            }
            
        return response()->json(["users" => $users, "user" => $user]);
    }

    public function deleteUserInfo(string $id)
    {
        $role = User::orderBy('id')
            ->where('id', $id)
            ->with(['student', 'faculty', 'staff'])
            ->value('role');

            if ($role === 'Student') {
                $studentId = DB::table('users')
                    ->join('students','users.id','students.user_id')
                    ->select('students.id as studentId','students.user_id')
                    ->where('students.user_id', $id)
                    ->value('studentId');

                $student = Student::findOrFail($studentId);
                $student->delete();

                $user = User::findOrFail($id);
                $user->delete();
            } elseif ($role === 'Staff') {
                $staffId = DB::table('users')
                    ->join('staff','users.id','staff.user_id')
                    ->select('staff.id as staffId','staff.user_id')
                    ->where('staff.user_id', $id)
                    ->value('staffId');

                $student = Staff::findOrFail($staffId);
                $student->delete();

                $user = User::findOrFail($id);
                $user->delete();
            } elseif ($role === 'Faculty') {
                $facultyId = DB::table('users')
                    ->join('faculty','users.id','faculty.user_id')
                    ->select('faculty.id as facultyId','faculty.user_id')
                    ->where('faculty.user_id', $id)
                    ->value('facultyId');

                $student = Faculty::findOrFail($staffId);
                $student->delete();

                $user = User::findOrFail($id);
                $user->delete();

            } elseif ($role === 'Admin') {
                $staffId = DB::table('users')
                    ->join('staff','users.id','staff.user_id')
                    ->select('staff.id as staffId','staff.user_id')
                    ->where('staff.user_id', $id)
                    ->value('staffId');

                $student = Staff::findOrFail($staffId);
                $student->delete();

                $user = User::findOrFail($id);
                $user->delete();
            }

        $data = array('success' =>'deleted','code'=>'200');
        return response()->json($data);
    }

    public function applicationlist()
    {
        $admin = DB::table('staff')
        ->join('users','users.id','staff.user_id')
        ->select('staff.*','users.*')
        ->where('user_id',Auth::id())
        ->first();

        $applications = DB::table('requestingform')
        ->join('files','files.id','requestingform.research_id')
        ->select('files.*','requestingform.*')
        ->get();

        return View::make('admin.applicationlist',compact('applications','admin'));
    }

    public function selectedSpecificStatus(Request $request)
    {
        $admin = DB::table('staff')
        ->join('users','users.id','staff.user_id')
        ->select('staff.*','users.*')
        ->where('user_id',Auth::id())
        ->first();

        if ($request->applicationStatus === 'Pending') {
            $applications = DB::table('requestingform')
            ->join('files','files.id','requestingform.research_id')
            ->select('files.*','requestingform.*')
            ->where('requestingform.status', 'Pending')
            ->get();
        } elseif ($request->applicationStatus === 'Passed') {
            $applications = DB::table('requestingform')
            ->join('files','files.id','requestingform.research_id')
            ->select('files.*','requestingform.*')
            ->where('requestingform.status', 'Passed')
            ->get();
        } elseif ($request->applicationStatus === 'Returned') {
            $applications = DB::table('requestingform')
            ->join('files','files.id','requestingform.research_id')
            ->select('files.*','requestingform.*')
            ->where('requestingform.status', 'Returned')
            ->get();
        } elseif ($request->applicationStatus === 'All') {
            $applications = DB::table('requestingform')
            ->join('files','files.id','requestingform.research_id')
            ->select('files.*','requestingform.*')
            ->get();
        }

        return View::make('admin.applicationlist',compact('applications','admin'));
    }

    public function showApplicationlistInfo($id)
    {
        $checker = DB::table('requestingform')
            ->where('id', $id)
            ->value('requestor_type');

        if ($checker === 'Faculty') {
            $specificData = DB::table('requestingform')
            ->join('files', 'files.id', 'requestingform.research_id')
            ->join('users', 'users.id', 'requestingform.user_id')
            ->leftJoin('certificates', 'certificates.id', 'requestingform.certificate_id')
            ->select(
                'requestingform.*', 
                'files.*',
                'certificates.certificate_file',
                'certificates.control_id',)
            ->where('requestingform.id', $id)
            ->first();
        } else {
            $specificData = DB::table('requestingform')
            ->join('files', 'files.id', 'requestingform.research_id')
            ->join('users', 'users.id', 'requestingform.user_id')
            ->join('faculty as technical_adviser', 'technical_adviser.id', '=', 'requestingform.technicalAdviser_id')
            ->join('faculty as subject_adviser', 'subject_adviser.id', '=', 'requestingform.subjectAdviser_id')
            ->leftJoin('certificates', 'certificates.id', 'requestingform.certificate_id')
            ->select(
                'requestingform.*', 
                'files.*',
                'certificates.certificate_file',
                'certificates.control_id',
                'technical_adviser.id as technical_adviser_id',
                'subject_adviser.id as subject_adviser_id',
                DB::raw("CONCAT(technical_adviser.fname, ' ', technical_adviser.lname, ' ', technical_adviser.mname) as TechnicalAdviserName"),
                DB::raw("CONCAT(subject_adviser.fname, ' ', subject_adviser.lname, ' ', subject_adviser.mname) as SubjectAdviserName"))
            ->where('requestingform.id', $id)
            ->first();
        }

        return response()->json($specificData);
    }

    public function deleteApplicationInfo(string $id)
    {
        $application = RequestingForm::findOrFail($id);
        $application->delete();
        $data = array('success' =>'deleted','code'=>'200');
        return response()->json($data);
    }

    public function researchlist()
    {
        $admin = DB::table('staff')
        ->join('users','users.id','staff.user_id')
        ->select('staff.*','users.*')
        ->where('user_id',Auth::id())
        ->first();

        $researches = Research::orderBy('id')->get();

        return View::make('admin.researchlist',compact('researches','admin'));
    }

    public function selectedSpecificDepartment(Request $request)
    {
        $admin = DB::table('staff')
        ->join('users','users.id','staff.user_id')
        ->select('staff.*','users.*')
        ->where('user_id',Auth::id())
        ->first();

        if ($request->researchDepartment === 'EAAD') {
            $researches = Research::orderBy('id')
            ->where('department', 'EAAD')
            ->get();
        } elseif ($request->researchDepartment === 'MAAD') {
            $researches = Research::orderBy('id')
            ->where('department', 'MAAD')
            ->get();
        } elseif ($request->researchDepartment === 'CAAD') {
            $researches = Research::orderBy('id')
            ->where('department', 'CAAD')
            ->get();
        } elseif ($request->researchDepartment === 'BASD') {
            $researches = Research::orderBy('id')
            ->where('department', 'BASD')
            ->get();
        } elseif ($request->researchDepartment === 'All') {
            $researches = Research::orderBy('id')->get();
        }

        return View::make('admin.researchlist',compact('researches','admin'));
    }

    public function showResearchInfo($id)
    {
        $research = Research::find($id);
        return response()->json($research);
    }

    public function deleteResearchInfo(string $id)
    {
        $research = Research::findOrFail($id);
        $research->delete();
        $data = array('success' =>'deleted','code'=>'200');
        return response()->json($data);
    }
    

    //MOBILE START
    public function dashboardmobile(Request $request)
    {
        $usersCount = DB::table('users')->count();
    
        $rolesCount = DB::table('users')
            ->select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get();
    
        $applicationsCount = DB::table('requestingform')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
    
        $thesisTypeCount = DB::table('requestingform')
            ->select('thesis_type', DB::raw('count(*) as count'))
            ->groupBy('thesis_type')
            ->get();
    
        $courseCount = DB::table('requestingform')
            ->select('course', DB::raw('count(*) as count'))
            ->groupBy('course')
            ->get();
    
        $researchDepartmentCount = DB::table('research_list')
            ->select('department', DB::raw('count(*) as count'))
            ->groupBy('department')
            ->get();
    
        $researchCourseCount = DB::table('research_list')
            ->select('course', DB::raw('count(*) as count'))
            ->groupBy('course')
            ->get();
    
        $studentCount = DB::table('users')->where('role', 'Student')->count();
    
        $staffCount = DB::table('users')->where('role', 'Staff')->count();
    
        $facultyCount = DB::table('users')->where('role', 'Faculty')->count();
    
        $applicationCount = DB::table('requestingform')->count();
    
        $pendingCount = DB::table('requestingform')
            ->join('files','files.id','requestingform.research_id')
            ->where('requestingform.status', '=', 'Pending')
            ->count();
    
        $passedCount = DB::table('requestingform')
            ->join('files','files.id','requestingform.research_id')
            ->where('requestingform.status', '=', 'Passed')
            ->count();
    
        $returnedCount = DB::table('requestingform')
            ->join('files','files.id','requestingform.research_id')
            ->where('requestingform.status', '=', 'Returned')
            ->count();
    
        $admin = DB::table('staff')
            ->join('users','users.id','staff.user_id')
            ->select('staff.*','users.*')
            ->where('user_id', Auth::id())
            ->first();
    
        $researchCount = DB::table('research_list')->count();
        $eaadResearchCount = DB::table('research_list')->where('department', 'EAAD')->count();
        $maadResearchCount = DB::table('research_list')->where('department', 'MAAD')->count();
        $basdResearchCount = DB::table('research_list')->where('department', 'BASD')->count();
        $caadResearchCount = DB::table('research_list')->where('department', 'CAAD')->count();
    
        $data = compact('usersCount', 'studentCount', 'staffCount', 'facultyCount', 'applicationCount', 'admin', 'pendingCount', 'passedCount', 'returnedCount', 'eaadResearchCount', 'maadResearchCount', 'caadResearchCount', 'basdResearchCount', 'researchCount', 'rolesCount', 'applicationsCount', 'thesisTypeCount', 'courseCount', 'researchDepartmentCount', 'researchCourseCount');
    
        return response()->json($data);
    }

    public function addAnnouncements(Request $request)
    {  
        // Your existing code for creating an announcement
        $announcement = new Announcement();
        $announcement->title = $request->title; 
        $announcement->content = $request->content;
        $announcement->user_id = $request->user_id; // Assuming this is a placeholder value
        $announcement->save();
        $announcement_id = $announcement->id;

        // Fetching images from the request
        $images = $request->file('images');

        if ($images) {
            foreach ($images as $index => $file) {
                $multi = [];

                // Assuming $this->announcement_img_upload() and other functions are defined elsewhere
                $this->announcement_img_upload($file);

                $multi['img_path'] = time() . $file->getClientOriginalName();
                $multi['announcements_id'] = $announcement_id;

                // You can adjust the method of storage or retrieval based on your requirements.
                DB::table('announcementsphoto')->insert($multi);
            }
        }

        // Prepare JSON response
        $response = [
            'success' => true,
            'message' => 'Announcement added successfully',
            'announcement_id' => $announcement_id,
        ];

        return response()->json($response);
    }

    public function listAnnouncement()
    {
        return Announcement::all();
    }

    public function mobileshowannouncement()
    {
        $student = DB::table('students')
            ->join('users', 'users.id', 'students.user_id')
            ->select('students.*', 'users.*')
            ->where('user_id', Auth::id())
            ->first();
    
        $staff = DB::table('staff')
            ->join('users', 'users.id', 'staff.user_id')
            ->select('staff.*', 'users.*')
            ->where('user_id', Auth::id())
            ->first();
    
        $faculty = DB::table('faculty')
            ->join('users', 'users.id', 'faculty.user_id')
            ->select('faculty.*', 'users.*')
            ->where('user_id', Auth::id())
            ->first();
    
        $admin = DB::table('staff')
            ->join('users', 'users.id', 'staff.user_id')
            ->select('staff.*', 'users.*')
            ->where('user_id', Auth::id())
            ->first();
    
        $announcements = DB::table('announcements')
            ->join('announcementsphoto', 'announcementsphoto.announcements_id', 'announcements.id')
            ->join('users', 'announcements.user_id', 'users.id')
            ->select(
                'users.fname',
                'users.lname',
                'users.mname',
                'users.role',
                'announcementsphoto.id as photo_id',
                'announcements.id as announcement_id',
                'announcements.title',
                'announcements.content',
                'announcementsphoto.img_path',
                DB::raw('TIME(announcements.created_at) as created_time')
            )
            ->orderBy('announcements.id')
            ->get()
            ->groupBy('announcement_id');
    
        $data = compact('admin', 'student', 'staff', 'faculty', 'announcements');
    
        // Return the data as JSON response
        return response()->json($data);
    }

    public function mobileadministration()
    {
        $admin = DB::table('staff')
            ->join('users', 'users.id', 'staff.user_id')
            ->select('staff.*', 'users.*')
            ->where('user_id', Auth::id())
            ->first();

        $adminlist = DB::table('staff')
            ->join('users', 'users.id', 'staff.user_id')
            ->select('staff.*', 'users.id as userid', 'users.role')
            ->get();

        $data = [
            'admin' => $admin,
            'adminlist' => $adminlist,
        ];

        return response()->json($data);
    }

    public function mobileaddAdministration(Request $request)
    {
        $users = new User();
        $users->fname = $request->admin_fname;
        $users->lname = $request->admin_lname;
        $users->mname = $request->admin_mname;
        $users->role = $request->admin_role;
        $users->email = $request->admin_email;
        $users->password = bcrypt($request->admin_password);
        $users->save();
        $lastid = DB::getPdo()->lastInsertId();

        $staff = new Staff();
        $staff->fname = $request->admin_fname;
        $staff->lname = $request->admin_lname;
        $staff->mname = $request->admin_mname;
        $staff->position = $request->admin_position;
        $staff->designation = $request->admin_designation;
        $staff->tup_id = $request->admin_id;
        $staff->email = $request->admin_email;
        $staff->gender = $request->admin_gender;
        $staff->phone = $request->admin_phone;
        $staff->address = $request->admin_address;
        $staff->birthdate = $request->admin_birthdate;
        $staff->user_id = $lastid;
        $staff->save();

        $response = [
            'message' => 'Administrator Added',
            'admin' => $users,
            'staff' => $staff,
        ];

        return response()->json($response);
    }

    public function mobileeditAdministration($id)
    {
        $staff = Staff::find($id);

        if (!$staff) {
            return response()->json(['error' => 'Staff not found'], 404);
        }

        return response()->json($staff);
    }

    public function mobileupdateAdministration(Request $request, $id)
    {
        $staff = Staff::find($id);
        $staff->fname = $request->fname;
        $staff->lname = $request->lname;
        $staff->mname = $request->mname;
        $staff->position = $request->position;
        $staff->designation = $request->designation;
        $staff->tup_id = $request->staffid;
        $staff->email = $request->email;
        $staff->gender = $request->gender;
        $staff->phone = $request->phone;
        $staff->address = $request->address;
        $staff->birthdate = $request->birthdate;
        $staff->save();

        $user_id = DB::table('staff')
        ->join('users','users.id','staff.user_id')
        ->select('users.id')
        ->where('staff.id',$id)
        ->first();

        $user = User::find($user_id->id);
        $user->fname = $request->fname;
        $user->lname = $request->lname;
        $user->mname = $request->mname;
        $user->email = $request->email;
        $user->save();

        return response()->json(["staff" => $staff, "user" => $user],201);
    }

    public function mobileeditAdministrationRole($id)
    {
        $admin = DB::table('staff')
        ->join('users','users.id','staff.user_id')
        ->select('staff.*','users.id as userid','users.role')
        ->where('staff.id', $id)
        ->first();

        return response()->json($admin);
    }

    public function mobileupdateAdministrationRole(Request $request, $id)
    {
        $user = User::find($request->roleId);
        $user->role = $request->role;
        $user->save();

        return response()->json($user);
    }

    public function mobiledeleteAdministration(string $id)
    {
        $staff = Staff::findOrFail($id);
        $staff->delete();
        $data = array('success' =>'deleted','code'=>'200');
        return response()->json($data);
    }
    //MOBILE END
    
}
