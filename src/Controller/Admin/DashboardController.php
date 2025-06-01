<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Params;
use App\Entity\AppFAQ;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Contact;
use App\Entity\ForumPost;
use App\Entity\ForumTopic;
use App\Entity\MaitreIslamique;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{ 
    public function index(): Response
    {
        return $this->render('admin/easyadmin/dashboard.html.twig');
    }
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Kong - Administration')
            ->setFaviconPath('favicon.ico');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');

        // Lien vers le site principal
        yield MenuItem::section('Liens');
        yield MenuItem::linkToRoute('Accueil', 'fa fa-undo', 'app_home');
        yield MenuItem::linkToRoute('Dashboard LTE', 'fa fa-undo', 'admin_dashboard');

        // Gestion des catégories
        yield MenuItem::section('Blog & Commentaires');
        yield MenuItem::linkToCrud('Catégories', 'fa fa-list', Category::class);
        yield MenuItem::linkToCrud('Commentaires', 'fa fa-comments', Comment::class);
        
        // Gestion des & Messages
        yield MenuItem::section('Forums & Messages');
        yield MenuItem::linkToCrud('Sujets Forum', 'fa fa-comments', ForumTopic::class);
        yield MenuItem::linkToCrud('Chat Forum', 'fa fa-folder', ForumPost::class);
        yield MenuItem::linkToCrud('Messages', 'fa fa-envelope', Contact::class);
        
        // Gestion des paramètres
        yield MenuItem::section('Configuration');
        yield MenuItem::linkToCrud('FAQ', 'fa fa-question-circle', AppFAQ::class);
        yield MenuItem::linkToCrud('Paramètres', 'fa fa-cogs', Params::class);
        
        // Gestion des utilisateurs et rôles
        yield MenuItem::section('Utilisateurs');
        yield MenuItem::linkToCrud('Maitres Islamiques', 'fa fa-book', MaitreIslamique::class);
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class);
    }
}
