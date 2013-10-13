<?php


use Silex\Application;
use Doctrine\DBAL\Connection;

/**
 * class PostsModel
 */
class PostsModel extends BaseModel
{

	public $filterUser;
	public $offset;
	public $limit;

	public $lastSQL; 

	function __construct( $db, Silex\Application $application )
	{
		parent::__construct($db, $application);
		$this->limit = 100;
		$this->offset = 0;
	}

	private function _postsSelect()
	{
		return "SELECT post.id as id, title, content, category, time, allow_comments, slug, username, user_id
					FROM post
					JOIN user ON user.id = post.user_id";
	}



	/**
	 * [_getPosts description]
	 * @param  string $category [description]
	 * @param  string $userID   [description]
	 * @return array            [description]
	 */
	private function _getPosts($category = POST_TYPE_ALL, $userID = '')
	{
		$select = $this->_postsSelect();

		

		// if both user and category are set
		if (($category != POST_TYPE_ALL) && ($userID != '')) 
		{
			$sql = "$select 
						WHERE category = ?
						AND username = ?
						AND display = 1 LIMIT {$this->offset}, {$this->limit}";
	
			$posts = $this->db->fetchAll($sql, array((string) $category, (string) $userID));	
		}			

		// if just category is set
		else if ($category != POST_TYPE_ALL) 
		{
			
			$sql = "$select 
						WHERE category = ? 
						AND display = 1 LIMIT {$this->offset}, {$this->limit}";					
	
			$posts = $this->db->fetchAll($sql, array((string) $category));						
		}

		// if just usertID is set
		else if ($userID != '') 
		{

			$sql = "$select 
						WHERE username = ?
						AND display = 1 LIMIT {$this->offset}, {$this->limit}";					
	
			$posts = $this->db->fetchAll($sql, array((string) $userID));						
		}

		// if neither category or user are set
		else {
			$sql = "$select 
						WHERE display = 1 LIMIT {$this->offset}, {$this->limit}";					
	
			$posts = $this->db->fetchAll($sql);						
		}

		// need to loop through each record and check if the post can be edited by the current user
		foreach($posts as &$post) 
		{
			if ($this->app['security']->isGranted('ROLE_USER')) {
				$user = $this->app['security']->getToken()->getUser();
				$post['can_edit'] = (( in_array('ROLE_ADMIN', $user->getRoles() )) || ($post['user_id'] == $user->getID()));
			}
			else $post['can_edit'] = false;
		}

    	return $posts;
	}

	public function fetchPostsByCategoryUser($category, $userID = '') 
	{
		return $this->_getPosts($category, $userID);	
	}

	/**
	 * [fetchPostsByCategory description]
	 * @param  string $category 	The category to fetch (can be ALL for fetch everything!
	 * @return array          		An arra
	 */
	public function fetchPostsByCategory($category) 
	{
		return $this->_getPosts($category);
	}

	/**
	 * Fetchs a single Post record, usually to display or edit
	 * @param  variable $id The identifier for the post.  Can be an integer (id) or a string (title)
	 * @return array     An array containing the post data
	 */
	public function fetchPost($id) {

		if (is_numeric($id)) 	$where = "post.id = $id";
		else 					$where = "post.slug = '$id'";

		$select = $this->_postsSelect($where);
		
		$sql = "$select
					WHERE $where";

		$post = $this->db->fetchAssoc($sql, array((string) $category));		

		if ($this->app['security']->isGranted('ROLE_USER')) {
			$user = $this->app['security']->getToken()->getUser();
			$post['can_edit'] = (( in_array('ROLE_ADMIN', $user->getRoles() )) || ($post['user_id'] == $user->getID()));
		}
		else $post['can_edit'] = false;				

		return $post; 				
	}

	/**
	 * Updates a POST record
	 * @param  integer $id   	id of the post to be updated
	 * @param  array $data   	an array containing the updated data (usually created by a Silex Form)
	 * @return handle      		handle to the PostController object
	 */
	public function updatePost($id, $data) 
	{
		$this->db->update('post', array(
            'title'             => $data['title'],
            'slug'          => $data['slug'],
            'content'           => $data['content'],
            'category'          => $data['category'],
            'allow_comments'    => $data['allow_comments'],
            ), array('id' => $id));

		return $this;
	}

	public function deletePost($id) 
	{
		$this->db->delete('post', array('id' => $id));
	}

	public function insertPost($data) 
	{
		$this->db->insert('post', array(
			'title' 			=> $data['title'],
			'slug' 			=> $data['slug'],
			'content' 			=> $data['content'],
			'category' 			=> $data['category'],
			'allow_comments' 	=> $data['allow_comments'],
			'user_id' 			=> $data['user_id'],
		));

		return $this->db->lastInsertId();
	}

	public function createForm($data, $categoryChoices) 
	{
		return $this->app['form.factory']->createBuilder('form', $data)
	        ->add('title', 'text', array(
	            'attr' => array('class' => 'form-control')
	        ))
	        ->add('slug', 'text', array(
	            'attr' => array('class' => 'form-control')
	        ))
	        ->add('content', 'textarea', array(
	            'attr' => array('class' => 'html-editor')
	        ))
	        ->add('category', 'choice', array(
	            'choices' => $categoryChoices,
	            'expanded' => false,
	            'multiple' => false,
	            'attr'=> array('class'=>'form-control')
	        ))
	        ->add('allow_comments', 'choice', array(
	            'choices' => array(1 => 'Yes', 0 => 'No'),
	            'expanded' => false,
	            'attr'=> array('class'=>'form-control')
	        ))
	        ->getForm();
	}
}

