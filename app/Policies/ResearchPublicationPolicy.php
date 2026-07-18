<?php
namespace App\Policies;
use App\Models\ResearchPublication;
use App\Models\User;
class ResearchPublicationPolicy
{
 private function owner(User $u,ResearchPublication $p):bool{return $u->hasRole('INVESTIGADOR')&&(int)$p->user_id===(int)$u->id;}
 public function viewAny(User $u):bool{return $u->can('research-publications.view-own')||($u->can('research-publications.view')&&!$u->hasRole('INVESTIGADOR'));}
 public function view(User $u,ResearchPublication $p):bool{return $this->owner($u,$p)||($u->can('research-publications.view')&&!$u->hasRole('INVESTIGADOR')&&!$p->isReviewDraft());}
 public function create(User $u):bool{return $u->hasRole('INVESTIGADOR')&&$u->can('research-publications.create');}
 public function update(User $u,ResearchPublication $p):bool{return $this->owner($u,$p)&&$p->isEditableByResearcher()&&$u->can('research-publications.edit-own');}
 public function delete(User $u,ResearchPublication $p):bool{return $this->owner($u,$p)&&$p->isReviewDraft()&&$p->status==='draft'&&$u->can('research-publications.delete-own');}
 public function submit(User $u,ResearchPublication $p):bool{return $this->owner($u,$p)&&$p->isEditableByResearcher()&&$u->can('research-publications.submit');}
 public function downloadPrivatePdf(User $u,ResearchPublication $p):bool{return ($this->owner($u,$p)&&$u->can('research-publications.download-own'))||($u->can('research-publications.view')&&!$u->hasRole('INVESTIGADOR')&&!$p->isReviewDraft());}
 public function updateEditorial(User $u,ResearchPublication $p):bool{return $u->can('research-publications.edit')&&!$u->hasRole('INVESTIGADOR')&&!$p->isReviewDraft();}
}
