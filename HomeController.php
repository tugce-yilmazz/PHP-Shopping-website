<?php

namespace App\Controller;


use App\Entity\Admin\Messages;
use App\Entity\User;
use App\Form\Admin\MessagesType;
use App\Form\UsersType;
use App\Repository\Admin\CategoryRepository;
use App\Repository\Admin\ProductRepository;
use App\Repository\Admin\SettingRepository;
use App\Repository\Admin\ImageRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(SettingRepository $settingRepository, CategoryRepository $categoryRepository)
    {
        $data= $settingRepository->findAll();
        $em= $this->getDoctrine()->getManager();
        $sql="SELECT * FROM product WHERE status='True' ORDER BY ID DESC LIMIT 5";
        $sql1="SELECT * FROM product WHERE status='True' ORDER BY ID DESC LIMIT 10";
        $statement=$em->getConnection()->prepare($sql);
        $statement1=$em->getConnection()->prepare($sql1);
        $statement->execute();
        $statement1->execute();
        $sliders = $statement->fetchAll();
        $products = $statement1->fetchAll();
        // dump($sliders);
        // die();

        $cats= $this->categorytree();
       // print_r($cats);
        // die();
        $cats[0]='<ul id="menu-v">';

        return $this->render('home/index.html.twig', [
            'data' => $data,
            'cats'=> $cats,
            'sliders'=> $sliders,
            'products'=>$products,
        ]);
    }
    /**
     * @Route("/hakkimizda", name="hakkimizda")
     */
    public function hakkimizda(SettingRepository $settingRepository)
    {
        $data= $settingRepository->findAll();
        return $this->render('home/hakkimizda.html.twig', [
            'data' => $data,
        ]);
    }
    /**
     * @Route("/referans", name="referans")
     */
    public function referans(SettingRepository $settingRepository)
    {
        $data= $settingRepository->findAll();
        return $this->render('home/referanslar.html.twig', [
            'data' => $data,
        ]);
    }
    /**
     * @Route("/iletisim", name="iletisim", methods="GET|POST")
     */
    public function iletisim(SettingRepository $settingRepository, Request $request)
    {
        $message = new Messages();
        $form = $this->createForm(MessagesType::class, $message);
        $form->handleRequest($request);

        $submittedToken=$request->request->get('token');
        if($form->isSubmitted()) {
            if ($this->isCsrfTokenValid('form-message', $submittedToken)) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($message);
                $entityManager->flush();
                $this->addFlash('success', 'Mesaj Gönderildi');

                return $this->redirectToRoute('iletisim');
            }
        }

        $data= $settingRepository->findAll();

        return $this->render('home/iletisim.html.twig', [
            'data' => $data,
            'message'=>$message,
        ]);
    }


    public function categorytree($parent=0,$user_tree_array='')
    {
       if(!is_array($user_tree_array))
           $user_tree_array=array();

        $em=$this->getDoctrine()->getManager();
        $sql="SELECT * FROM category WHERE status='True' AND parentid=".$parent;
        $statement= $em ->getConnection()->prepare($sql);
     //   $statement->bindValue('parentid',$parent);
        $statement->execute();
        $result= $statement->fetchAll();

        if(count($result)>0){
            $user_tree_array[]="<ul>";
            foreach ($result as $row){
                $user_tree_array[]="<li> <a href='/category/".$row['id']."'>".$row['title']."</a>";
                $user_tree_array=$this->categorytree($row['id'],$user_tree_array);

            }
            $user_tree_array[]="</li></ul>";
        }
        return $user_tree_array;
    }

    /**
     * @Route("/category/{catid}", name="category_products", methods="GET")
     */
    public function CategoryProducts($catid,CategoryRepository $categoryRepository)
    {
        $cats= $this->categorytree();

        $data=$categoryRepository->findBy(
            ['id'=>$catid]
        );
        $cats[0]='<ul id="menu-v">';
       //  dump($data);
        $em=$this->getDoctrine()->getManager();
        $sql='SELECT * FROM product WHERE status="True" AND category_id=:catid';
        $statement= $em ->getConnection()->prepare($sql);
        $statement->bindValue('catid',$catid);
        $statement->execute();
        $products= $statement->fetchAll();
        // dump($result);
        // die();
        return $this->render('home/products.html.twig', [
            'data' => $data,
            'products'=> $products,
            'cats'=> $cats,
        ]);

    }

    /**
     * @Route("/product/{id}", name="product_detail", methods="GET")
     */
    public function ProductDetail($id,ProductRepository $productRepository ,ImageRepository $imageRepository)
    {


        $data=$productRepository->findBy(
            ['id'=>$id]
        );

        $images=$imageRepository->findBy(
            ['product_id'=>$id]
        );

        $cats=$this->categorytree();
        $cats[0]='<ul id="menu-v">';

        return $this->render('home/product_detail.html.twig', [
            'data' => $data,
            'cats' => $cats,
            'images' => $images,

        ]);

    }

    /**
     * @Route("newuser", name="new_user", methods="GET|POST")
     */
    public function newuser(Request $request,UserRepository $userRepository):Response
    {
        $user = new User();
        $form = $this -> createForm(UsersType::class,$user);
        $form->handleRequest($request);

        $submittedToken = $request->request->get('token');


        if($this->isCsrfTokenValid('user-form',$submittedToken)){
            if($form->isSubmitted()){

                $emaildata=$userRepository->findBy(
                    ['email'=>$user->getEmail()]
                );
                if($emaildata==null){

                $em= $this->getDoctrine()->getManager();
                $user->setRoles("ROLE_USER");
                $em->persist($user);
                $em->flush();
                $this->addFlash('success', 'Üye kaydınız başarı ile gerçekleştirilmiştir.');

                return $this->redirectToRoute('app_login');
            }
            else{
                $this->addFlash('error', $user->getEmail()." Email adresi kayıtlı");

                return $this->render('home/newuser.html.twig',[
                    'form' => $form->createView(),
                    'user' =>$user,
                ]);
            }
            }

        }

        return $this->render('home/newuser.html.twig',[
            'form' => $form->createView(),
            'user' =>$user,
        ]);
    }



}
