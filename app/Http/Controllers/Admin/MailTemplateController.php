<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MailTemplate;
use App\Http\Controllers\MailTestController;

class MailTemplateController extends Controller
{
    public function index()
    {
        $templates = MailTemplate::orderBy('category')->orderBy('name')->paginate(20);
        return view('admin.mailtemplates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.mailtemplates.form', ['template' => new MailTemplate]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'=>'required',
            'slug'=>'required|unique:mail_templates,slug',
            'category'=>'nullable|string',
            'locale'=>'nullable|string',
            'subject'=>'required',
            'body'=>'required',
            'is_active'=>'nullable|boolean'
        ]);
        $data['category'] = $data['category'] ?? 'sistema';
        $data['locale'] = $data['locale'] ?? 'pt-BR';
        $data['is_active'] = $request->boolean('is_active', true);
        MailTemplate::create($data);
        return redirect()->route('admin.mailtemplates.index')->with('success','Template salvo');
    }

    public function edit(MailTemplate $mailtemplate)
    {
        return view('admin.mailtemplates.form', ['template' => $mailtemplate]);
    }

    public function update(Request $request, MailTemplate $mailtemplate)
    {
        $data = $request->validate([
            'name'=>'required',
            'slug'=>'required|unique:mail_templates,slug,'.$mailtemplate->id,
            'category'=>'nullable|string',
            'locale'=>'nullable|string',
            'subject'=>'required',
            'body'=>'required',
            'is_active'=>'nullable|boolean'
        ]);
        $data['category'] = $data['category'] ?? 'sistema';
        $data['locale'] = $data['locale'] ?? 'pt-BR';
        $data['is_active'] = $request->boolean('is_active', true);
        $mailtemplate->update($data);
        return redirect()->route('admin.mailtemplates.index')->with('success','Template atualizado');
    }

    public function destroy(MailTemplate $mailtemplate)
    {
        $mailtemplate->delete();
        return redirect()->route('admin.mailtemplates.index')->with('success','Template removido');
    }

    public function preview(MailTemplate $mailtemplate)
    {
        $data = $this->sampleData();
        $html = $this->renderVariables($mailtemplate->body, $data);
        return response()->json(['html' => $html]);
    }

    public function sendPreview(Request $request, MailTemplate $mailtemplate)
    {
        $request->validate(['email'=>'required|email']);
        $data = $this->sampleData($request->input('email'));
        $html = $this->renderVariables($mailtemplate->body, $data);

        $mt = new MailTestController();
        return $mt->sendRaw($request->input('email'), $mailtemplate->subject, $html);
    }

    protected function sampleData($email = 'preview@example.com')
    {
        $logo = \App\Models\Setting::where('key', 'logo_image')->value('value');
        if(!$logo) $logo = \App\Models\Setting::where('key', 'logo_admin')->value('value');
        if(!$logo) $logo = \App\Models\Setting::where('key', 'logo_auth')->value('value');
        $logoUrl = $logo ? asset($logo) : asset('img/logo.svg');

        return [
            'user' => [
                'name' => 'Usuário Exemplo',
                'email' => $email,
                'phone' => '(21) 99999-9999',
                'level' => 'sucesso',
                'points' => 1200
            ],
            'site' => [
                'name' => config('app.name'),
                'url' => url('/'),
                'support_email' => config('mail.from.address'),
                'logo' => $logoUrl
            ],
            'order' => [
                'id' => 'PED-12345',
                'total' => 'R$ 497,00',
                'status' => 'Aprovado',
                'date' => now()->format('d/m/Y H:i')
            ],
            'payment' => [
                'due_date' => now()->addDays(3)->format('d/m/Y'),
                'link' => url('/pagamento/boleto')
            ],
            'event' => [
                'title' => 'Mentoria Elite',
                'date' => now()->addWeek()->format('d/m/Y H:i'),
                'link' => url('/eventos/mentoria-elite')
            ],
            'course' => ['title' => 'Curso de Networking'],
            'mentorship' => ['title' => 'Mentoria Premium'],
        ];
    }

    protected function renderVariables($body, $data)
    {
        try {
            $rendered = \Blade::render($body, $data);
        } catch (\Throwable $e) {
            $rendered = $body;
            foreach($data as $k => $v){
                if(is_array($v)){
                    foreach($v as $subk => $subv){
                        $rendered = str_replace('{{'.$k.'.'.$subk.'}}', $subv, $rendered);
                    }
                } else {
                    $rendered = str_replace('{{'.$k.'}}', $v, $rendered);
                }
            }
        }
        
        // Allowed tags
        $allowed = '<p><a><strong><em><ul><ol><li><br><img><table><tr><td><th><tbody><thead><h1><h2><h3><h4><h5><span><div><style><center>'; 
        $content = strip_tags($rendered, $allowed);

        // System Colors
        $primaryColor = \App\Models\Setting::where('key', 'color_primary')->value('value') ?? '#007bff';
        $secondaryColor = \App\Models\Setting::where('key', 'color_secondary')->value('value') ?? '#6c757d';
        
        // Wrap with layout
        $layout = '
        <div style="background-color: #f4f6f9; padding: 20px; font-family: sans-serif; min-height: 100%;">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td align="center">
                        <div style="background-color: #ffffff; max-width: 600px; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                            <!-- Header -->
                            <div style="text-align: center; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 2px solid '.$primaryColor.';">
                                <img src="'.$data['site']['logo'].'" alt="{{site.name}}" style="max-height: 60px; max-width: 200px;">
                            </div>
                            
                            <!-- Body -->
                            <div style="color: #333333; line-height: 1.6;">
                                '.$content.'
                            </div>
                            
                            <!-- Footer -->
                            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eeeeee; text-align: center; color: #777777; font-size: 12px;">
                                <p>&copy; '.date('Y').' '.$data['site']['name'].'. Todos os direitos reservados.</p>
                                <p><a href="'.$data['site']['url'].'" style="color: '.$primaryColor.'; text-decoration: none;">Visite nosso site</a></p>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>';

        return $layout;
    }
}