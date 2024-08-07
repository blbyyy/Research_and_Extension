@extends('layouts.navigation')
@include('sweetalert::alert')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
  .icon{
      font-size: 8em;
      display: flex;
      justify-content: center;
      align-items: center;
      padding-top: 30px;
      padding-bottom: 50px;
      color: maroon;
  }
  .body{
      display: flex;
      justify-content: center;
      align-items: center;
      padding-bottom: 50px;
  }
</style>
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Extension Proposals</h1>
    </div>

    @if(session('success'))
      <script>
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: '{{ session('success') }}',
          });
      </script>
    @elseif(session('error'))
      <script>
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: '{{ session('error') }}',
          });
      </script>
    @endif

    <div class="card">
        <div class="card-body">
          <h5 class="card-title">List of Extension Proposal</h5>

          @if(count($proposals) > 0)
            <table class="table table-hover">
              <thead>
                  <tr class="text-center">
                      <th scope="col">Actions</th>
                      <th scope="col">Application Title</th>
                      <th scope="col">Requestor</th>
                      <th scope="col">Status</th>
                  </tr>
              </thead>
              <tbody> 
                  @foreach($proposals as $proposal)
                      <tr class="text-center">
                          <td>
                            @if ($proposal->status === 'New Application') 
                            <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application currently added"><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Pending Approval for Proposal Consultation Appointment') 
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application is waiting for approval of proposal consultation appointment."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Appointment Set for Proposal Consultation')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application is ongoing to proposal consultation please wait to be done."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Appointment Done for Proposal Consultation')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application was done to proposal consultation."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Proposal Consultation Appointment Cancelled')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="Proposal consultation was cancelled; let the requestor make another schedule to proceed."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Pending Approval of R&E Office')
                              <button data-id="{{$proposal->id}}" type="button" class="btn btn-primary processProposal1" data-bs-toggle="modal" data-bs-target="#processingProposal1"><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Proposal Approved by R&E Office')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application already approved."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Proposal Rejected by R&E Office')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application was rejected."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Pending Approval of DO, UES and President') 
                              <button data-id="{{$proposal->id}}" type="button" class="btn btn-primary processProposal2" data-bs-toggle="modal" data-bs-target="#processingProposal2"><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Proposal Rejected By DO')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application was rejected by DO."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Proposal Rejected By UES')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application was rejected by UES."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Proposal Rejected By President')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application was rejected by President."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Proposal Approved By DO, UES and President')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application already approved by do, ues and president."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Pending Approval of Board and OSG')
                              <button data-id="{{$proposal->id}}" type="button" class="btn btn-primary processProposal5" data-bs-toggle="modal" data-bs-target="#processingProposal5"><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Proposal Rejected By Board')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application was rejected by Board."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Proposal Rejected By OSG')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application was rejected by OSG."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Proposal Approved By Board and OSG')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application already approved by OSG."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Pending Approval for Implementation Proper Appointment')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application waiting for approval of implementation proper appointment."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Appointment Set for Implementation Proper')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application is ongoing to implementation proper appointment please wait to be done."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Appointment Done for Implementation Proper')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application was done to implementation proper."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Topics and Sub Topics Inputted')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application need to make an appointment for consultation about pre-evaluation survey."><i class="bi bi-arrow-right"></i></button>
                            {{-- @elseif ($proposal->status === 'Pending Implementation Approval by R&E-Office') 
                              <button data-id="{{$proposal->id}}" type="button" class="btn btn-primary processProposal7" data-bs-toggle="modal" data-bs-target="#processingProposal7"><i class="bi bi-arrow-right"></i></button> --}}
                            @elseif ($proposal->status === 'Pending Approval for Pre-Survey Consultation Appointment')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application waiting for approval of admin for pre-survey consultation appointment."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Appointment Cancelled for Pre-Survey Consultation')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application was cancelled the appointment abnout pre-survey consultation."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Appointment Set for Pre-Survey Consultation')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application is ongoing to consultation about Pre-Survey "><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Appointment Done for Pre-Survey Consultation')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application need to make an appointment for consultation about mid-evaluation survey."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Pending Approval for Mid-Survey Consultation Appointment')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application waiting for approval of admin for mid-survey consultation appointment."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Appointment Set for Mid-Survey Consultation')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application is ongoing; please wait to done to proceed next step."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Appointment Done for Mid-Survey Consultation')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application is done to mid-evaluation survey."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Appointment Cancelled for Mid-Survey Consultation')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application was cancelled the appointment abnout mid-survey consultation."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Inserted: Certificate, Documentation, Attendance, and Capsule Details')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application is almost done wait to the owner response if they have ptototype."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Have Prototype: Letter, NDA, COA Inserted')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="The prototype pre-evaluation survey is the next step in this application."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Prototype Pre-Evaluation Survey Done')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="The prototype mid-evaluation survey is the next step in this application."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Prototype Pre-Evaluation Survey Not Done')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="prototype pre-evaluation survey not done."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Prototype Mid-Evaluation Survey Done')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="The prototype post-evaluation survey is the next step in this application."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Prototype Mid-Evaluation Survey Not Done')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="prototype mid-evaluation survey not done."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Prototype Post-Evaluation Survey Done')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="Uploading Capsule Detail/Narative, Certificate, Documentation Photos and Attendancce is the next step in this application."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Prototype Post-Evaluation Survey Not Done')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="prototype post-evaluation survey not done."><i class="bi bi-arrow-right"></i></button>
                            @elseif ($proposal->status === 'Process Done')
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application is completed."><i class="bi bi-arrow-right"></i></button>
                            
                            @elseif ($proposal->status === 'Proposal Rejected') 
                              <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="This application has been rejected."><i class="bi bi-arrow-right"></i></button>
                          
                            @endif
                          </td> 
                          <td>{{$proposal->title}}</td>
                          <td>
                              {{$proposal->requestor_name}}
                              <span style="font-size: small">({{$proposal->role}})</span>
                          </td>
                          <td>
                            @if ($proposal->status == 'Process Done')
                              <span class="badge rounded-pill bg-success">{{$proposal->status}}</span>
                            @else
                              <span class="badge rounded-pill bg-primary">{{$proposal->status}}</span> 
                            @endif
                          </td>
                      </tr> 
                  @endforeach
              </tbody>
            </table>
          @else
            <table class="table table-hover">
              <thead>
                  <tr class="text-center">
                      <th scope="col">Actions</th>
                      <th scope="col">Application Title</th>
                      <th scope="col">Requestor</th>
                      <th scope="col">Status</th>
                  </tr>
              </thead>
              <tbody>
                  <tr>
                  </tr> 
              </tbody>
            </table>
            <div class="alert alert-danger" role="alert">
                <div class="text-center">
                    <span class="badge border-danger border-1 text-danger" style="font-size: large">No Extension Proposal Populated</span>
                </div>
            </div>
          @endif

        <div class="modal fade" id="processingProposal1" tabindex="-1">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Extension Application</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="text-center" style="padding-bottom: 30px; padding-top: 30px;">
                      <h5><b style="color: maroon">Requestor:</b></h5>
                      <p id="requestor"></p>
                      <h5><b style="color: maroon">Partner/Beneficiary:</b></h5>
                      <p id="beneficiary"></p>
                      <h5><b style="color: maroon">Reference File:</b></h5>
                      <div class="row">
                        <i class="bx bxs-file-pdf" style="font-size: 4em; color: maroon;"></i>
                        <p id="mou_file" class="col-md-12"></p>
                      </div>
                    </div>

                    <form class="row g-3" method="POST" action="{{ route('admin.proposal.list.specific.sent1') }}" enctype="multipart/form-data">
                            @csrf

                        <input name="proposalId1" type="hidden" class="form-control" id="proposalId1">

                        <div class="col-12 text-center">
                          <label for="status" class="form-label">Is the proposal approved?</label>
                            <select id="status" class="form-select" name="status">
                                <option selected>Choose....</option>
                                <option value="Proposal Approved by R&E Office">Approve</option>
                                <option value="Proposal Rejected by R&E Office">Reject</option>
                            </select>
                        </div>

                        <div class="col-12" style="padding-top: 20px">
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-outline-dark">Submit</button>
                            </div>
                        </div>

                    </form>
                    
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div>

        </div>

        <div class="modal fade" id="processingProposal2" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Extension Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
               
                  <div class="text-center" style="padding-bottom: 30px; padding-top: 30px;">
                      <h5><b style="color: maroon">Application Title:</b></h5>
                      <p id="proposal2Title"></p>
                      <h5><b style="color: maroon">Requestor:</b></h5>
                      <p id="proposal2Requestor"></p>
                      <h5><b style="color: maroon">Status:</b></h5>
                      <p id="proposal2Status"></p>
                      <h5><b style="color: maroon">Reference File:</b></h5>
                      <div class="row">
                        <p id="proposal2Mou" class="col-md-6"></p>
                        <p id="proposal2Ppmp" class="col-md-6"></p>
                        <p id="proposal2Pr" class="col-md-6"></p>
                        <p id="proposal2MarketStudy" class="col-md-6"></p>
                      </div>
                  </div>

                  <form class="row g-3" method="POST" action="{{ route('admin.proposal.list.specific.sent2') }}" enctype="multipart/form-data">
                          @csrf

                      <input name="proposalId2" type="hidden" class="form-control" id="proposalId2">

                      <div class="col-12 text-center">
                        <label for="statusProposal2" class="form-label">Are the documents approved?</label>
                        <select id="statusProposal2" class="form-select" name="statusProposal2">
                          <option selected>Choose....</option>
                          <option value="Proposal Approved By DO, UES and President">Approve</option>
                          <option value="Proposal Rejected By DO">Reject By DO</option>
                          <option value="Proposal Rejected By UES">Reject By UES</option>
                          <option value="Proposal Rejected By President">Reject By President</option>
                        </select>
                      </div>

                      <div id="proposal2RemarksStatus" style="padding-top: 20px; display: none;" >
                        <div class="col-12 text-center">
                            <label for="proposal2Remarks">Remaarks/Issue</label>
                            <textarea class="form-control" id="proposal2Remarks" name="proposal2Remarks" style="height: 100px;"></textarea>
                        </div>
                      </div>

                      <div class="col-12" style="padding-top: 20px">
                          <div class="d-flex justify-content-end">
                              <button type="submit" class="btn btn-outline-dark">Submit</button>
                          </div>
                      </div>

                  </form>
                  
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>

        <div class="modal fade" id="processingProposal3" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Extension Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
               
                  <div class="text-center" style="padding-bottom: 30px; padding-top: 30px;">
                      <h5><b style="color: maroon">Application Title:</b> <span id="proposal3Title"></span></h5>
                      <h5><b style="color: maroon">Requestor:</b> <span id="proposal3Requestor"></span></h5>
                      <h5><b style="color: maroon">Status:</b> <span id="proposal3Status"></span></h5>
                      <h5><b style="color: maroon">Reference File:</b></h5>
                      <p id="proposal3Mou"></p>
                      <p id="proposal3Ppmp"></p>
                      <p id="proposal3Pr"></p>
                      <p id="proposal3MarketStudy"></p>
                  </div>

                  <form class="row g-3" method="POST" action="{{ route('admin.proposal.list.specific.sent3') }}" enctype="multipart/form-data">
                      @csrf

                      <input name="proposalId3" type="hidden" class="form-control" id="proposalId3">

                      <div class="col-12 text-center">
                        <label for="status" class="form-label">Are the documents approved?</label>
                        <select id="status" class="form-select" name="status">
                          <option selected>Choose....</option>
                          <option value="Pending Proposal Approval By President">Approve</option>
                          <option value="Proposal Rejected By UES">Reject</option>
                        </select>
                      </div>

                      <div class="col-12" style="padding-top: 20px">
                          <div class="d-flex justify-content-end">
                              <button type="submit" class="btn btn-outline-dark">Submit</button>
                          </div>
                      </div>
                  </form>
                  
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>

        <div class="modal fade" id="processingProposal4" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Extension Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
               
                  <div class="text-center" style="padding-bottom: 30px; padding-top: 30px;">
                      <h5><b style="color: maroon">Application Title:</b> <span id="proposal4Title"></span></h5>
                      <h5><b style="color: maroon">Requestor:</b> <span id="proposal4Requestor"></span></h5>
                      <h5><b style="color: maroon">Status:</b> <span id="proposal4Status"></span></h5>
                      <h5><b style="color: maroon">Reference File:</b></h5>
                      <p id="proposal4Mou"></p>
                      <p id="proposal4Ppmp"></p>
                      <p id="proposal4Pr"></p>
                      <p id="proposal4MarketStudy"></p>
                  </div>

                  <form class="row g-3" method="POST" action="{{ route('admin.proposal.list.specific.sent4') }}" enctype="multipart/form-data">
                      @csrf

                      <input name="proposalId4" type="hidden" class="form-control" id="proposalId4">

                      <div class="col-12 text-center">
                        <label for="status" class="form-label">Are the documents approved?</label>
                        <select id="status" class="form-select" name="status">
                          <option selected>Choose....</option>
                          <option value="Proposal Approved By President">Approve</option>
                          <option value="Proposal Rejected By President">Reject</option>
                        </select>
                      </div>

                      <div class="col-12" style="padding-top: 20px">
                          <div class="d-flex justify-content-end">
                              <button type="submit" class="btn btn-outline-dark">Submit</button>
                          </div>
                      </div>
                  </form>
                  
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>

        <div class="modal fade" id="processingProposal5" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Extension Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
               
                  <div class="text-center" style="padding-bottom: 30px; padding-top: 30px;">
                      <h5><b style="color: maroon">Application Title:</b></h5>
                      <p id="proposal5Title"></p>
                      <h5><b style="color: maroon">Requestor:</b> <span id="proposal5Requestor"></span></h5>
                      <h5><b style="color: maroon">Status:</b> <span id="proposal5Status"></span></h5>
                      <h5><b style="color: maroon">Reference File:</b></h5>
                      <div class="row">
                        <p id="proposal5Mou" class="col-md-6"></p>
                        <p id="proposal5Ppmp" class="col-md-6"></p>
                        <p id="proposal5Pr" class="col-md-6"></p>
                        <p id="proposal5MarketStudy" class="col-md-6"></p>
                      </div>
                  </div>

                  <form class="row g-3" method="POST" action="{{ route('admin.proposal.list.specific.sent5') }}" enctype="multipart/form-data">
                      @csrf

                      <input name="proposalId5" type="hidden" class="form-control" id="proposalId5">

                      <div class="col-12 text-center">
                        <label for="statusProposal5" class="form-label">Are the documents approved?</label>
                        <select id="statusProposal5" class="form-select" name="statusProposal5">
                          <option selected>Choose....</option>
                          <option value="Proposal Approved By Board and OSG">Approve</option>
                          <option value="Proposal Rejected By Board">Reject By Board</option>
                          <option value="Proposal Rejected By OSG">Reject By OSG</option>
                        </select>
                      </div>

                      <div id="proposal5RemarksStatus" style="padding-top: 20px; display: none;" >
                        <div class="col-12 text-center">
                            <label for="proposal5Remarks">Remaarks/Issue</label>
                            <textarea class="form-control" id="proposal5Remarks" name="proposal5Remarks" style="height: 100px;"></textarea>
                        </div>
                      </div>

                      <div class="col-12" style="padding-top: 20px">
                          <div class="d-flex justify-content-end">
                              <button type="submit" class="btn btn-outline-dark">Submit</button>
                          </div>
                      </div>
                  </form>
                  
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>

        <div class="modal fade" id="processingProposal6" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Extension Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
               
                  <div class="text-center" style="padding-bottom: 30px; padding-top: 30px;">
                      <h5><b style="color: maroon">Application Title:</b> <span id="proposal6Title"></span></h5>
                      <h5><b style="color: maroon">Requestor:</b> <span id="proposal6Requestor"></span></h5>
                      <h5><b style="color: maroon">Status:</b> <span id="proposal6Status"></span></h5>
                      <h5><b style="color: maroon">Reference File:</b></h5>
                      <p id="proposal6Mou"></p>
                      <p id="proposal6Ppmp"></p>
                      <p id="proposal6Pr"></p>
                      <p id="proposal6MarketStudy"></p>
                  </div>

                  <form class="row g-3" method="POST" action="{{ route('admin.proposal.list.specific.sent6') }}" enctype="multipart/form-data">
                      @csrf

                      <input name="proposalId6" type="hidden" class="form-control" id="proposalId6">

                      <div class="col-12 text-center">
                        <label for="status" class="form-label">Are the documents approved?</label>
                        <select id="status" class="form-select" name="status">
                          <option selected>Choose....</option>
                          <option value="Proposal Approved By OSG">Approve</option>
                          <option value="Proposal Rejected By OSG">Reject</option>
                        </select>
                      </div>

                      <div class="col-12" style="padding-top: 20px">
                          <div class="d-flex justify-content-end">
                              <button type="submit" class="btn btn-outline-dark">Submit</button>
                          </div>
                      </div>
                  </form>
                  
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>

        <div class="modal fade" id="processingProposal7" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Extension Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
               
                  <div class="text-center" style="padding-bottom: 30px; padding-top: 30px;">
                      <h5><b style="color: maroon">Requestor:</b> <span id="proposal7Requestor"></span></h5>
                      <h5><b style="color: maroon">Implementation Proper:</b> <span id="proposal7ImplementationProper"></span></h5>
                      <h5 id="p1"><b style="color: maroon">Proponent 1:</b> <span id="proposal7Proponent1"></span></h5>
                      <h5 id="p2"><b style="color: maroon">Proponent 2:</b> <span id="proposal7Proponent2"></span></h5>
                      <h5 id="p3"><b style="color: maroon">Proponent 3:</b> <span id="proposal7Proponent3"></span></h5>
                      <h5 id="p4"><b style="color: maroon">Proponent 4:</b> <span id="proposal7Proponent4"></span></h5>
                      <h5 id="p5"><b style="color: maroon">Proponent 5:</b> <span id="proposal7Proponent5"></span></h5>
                      <h5><b style="color: maroon">Status:</b> <P id="proposal7Status"></p></h5>
                  </div>

                  <form class="row g-3" method="POST" action="{{ route('admin.proposal.list.specific.sent7') }}" enctype="multipart/form-data">
                      @csrf

                      <input name="proposalId7" type="hidden" class="form-control" id="proposalId7">

                      <div class="col-12 text-center">
                        <label for="status" class="form-label">Is the implementation approved?</label>
                        <select id="status" class="form-select" name="status">
                          <option selected>Choose....</option>
                          <option value="Implementation Approved By R&E-Office">Approve</option>
                          <option value="Implementation Rejected By R&E-Office">Reject</option>
                        </select>
                      </div>

                      <div class="col-12" style="padding-top: 20px">
                          <div class="d-flex justify-content-end">
                              <button type="submit" class="btn btn-outline-dark">Submit</button>
                          </div>
                      </div>
                  </form>
                  
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>

        <div class="modal fade" id="processingProposal8" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Extension Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
               
                  <div class="text-center" style="padding-bottom: 30px; padding-top: 30px;">
                      <h5><b style="color: maroon">Requestor:</b> <span id="proposal8Requestor"></span></h5>
                      <h5><b style="color: maroon">Application Unique ID:</b> <span id="proposal8UniqueId"></span></h5>
                      <h5><b style="color: maroon">Status:</b> <P id="proposal8Status"></p></h5>
                  </div>

                  <form class="row g-3" method="POST" action="{{ route('admin.proposal.list.specific.sent7') }}" enctype="multipart/form-data">
                      @csrf

                      <input name="proposalId7" type="hidden" class="form-control" id="proposalId7">

                      <div class="col-md-12">
                          <div class="form-floating">
                              <select name="status" class="form-select" id="status" aria-label="State">
                                  <option selected>Choose....</option>
                                  <option value="Proposal Approved By R&E-Office">Approve</option>
                                  <option value="Proposal Rejected By R&E-Office">Reject</option>
                              </select>
                              <label for="status">Status</label>
                          </div>
                      </div>

                      <div class="col-12" style="padding-top: 20px">
                          <div class="d-flex justify-content-end">
                              <button type="submit" class="btn btn-outline-dark">Submit</button>
                          </div>
                      </div>
                  </form>
                  
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>

      </div>
        
      
    </div>

</main>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        document.getElementById('statusProposal2').addEventListener('change', function () {
            var proposal2RemarksStatus = document.getElementById('proposal2RemarksStatus');

            if (this.value != 'Proposal Approved By DO, UES and President') {
              proposal2RemarksStatus.style.display = 'block';
            } else {
              proposal2RemarksStatus.style.display = 'none';
            }
        });

        $('#processingProposal2').on('hidden.bs.modal', function () {
                $('#proposal2RemarksStatus').hide();
        });

        document.getElementById('statusProposal5').addEventListener('change', function () {
            var proposal5RemarksStatus = document.getElementById('proposal5RemarksStatus');

            if (this.value != 'Proposal Approved By Board and OSG') {
              proposal5RemarksStatus.style.display = 'block';
            } else {
              proposal5RemarksStatus.style.display = 'none';
            }
        });

        $('#processingProposal5').on('hidden.bs.modal', function () {
                $('#proposal5RemarksStatus').hide();
        });
    });
</script>